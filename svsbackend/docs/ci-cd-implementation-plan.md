# SVS Schools CI/CD Implementation Plan

## Goal

Automate validation and Docker image publishing from the `dipak` branch, then deploy the published image to the production server through a controlled pull, migration, cache preparation, and smoke-test sequence.

This document is the implementation and deployment guide. The GitHub Actions workflow is at [`.github/workflows/svsbackend-docker-publish.yml`](../.github/workflows/svsbackend-docker-publish.yml); it tests, builds, smoke-tests, and publishes images on pushes to `dipak`. Server-side deployment remains operator-run and must follow the steps below. This document does not contain live credentials. Put credential values in GitHub Actions secrets or the server's protected environment file, never in Git, a Docker image, or this document.

## Current project facts

- Laravel 12 app, PHP 8.2, Apache, MySQL 8, and Redis 7.
- [Dockerfile](../Dockerfile) builds Vite assets, production Composer dependencies, and the Apache runtime image.
- [docker-compose.yml](../docker-compose.yml) currently builds `svschool-app:local`, sets development values (`APP_DEBUG=true`), and publishes Apache as host port `9000`.
- [docker/entrypoint.sh](../docker/entrypoint.sh) clears the config cache at startup, conditionally migrates when `RUN_MIGRATIONS=true`, and clears the application and view caches. It does not create optimized config/route/view caches.
- [deploy/deployment.txt](../deploy/deployment.txt) identifies the Laravel/MySQL/Apache stack but has no deployment procedure.
- [.dockerignore](../.dockerignore) excludes `.env`, dependencies, and built frontend files; dependencies and assets are built into the image.
- Docker documentation currently has a port mismatch: [docker/README.md](../docker/README.md) describes host port `8000`, while Compose maps host port `9000` to container port `8000`.
- The current compose file is locally modified. Review and preserve that work when implementing production changes.

## Intended release flow

1. Developer commits and pushes changes to `dipak`. for test 2
2. GitHub Actions checks out the Laravel app, runs validation, builds the production image, starts it for an image smoke test, and publishes it to Docker Hub.
3. Each successful build is published with an immutable commit-SHA tag and the moving `latest` tag. The SHA tag is the deployment/rollback reference; `latest` remains available as requested.
4. The operator deploys on the server by pulling the selected image tag, running database migrations and cache preparation, restarting the app, and performing production health checks.
5. If checks fail, redeploy the previous known-good SHA tag and investigate before retrying.

The initial deployment model is operator-run on the server. It requires Docker Hub pull credentials on the server but does not require a GitHub-to-server SSH key. Automated SSH deployment can be added later if desired, with a separate restricted deployment key.

## Step-by-step implementation plan

### Step 1 — Confirm repository and release access

1. On GitHub, open the repository that contains this project. Confirm its **Code** tab shows this Laravel app under `svsbackend/` and that `dipak` exists under **Branches**. The workflow must be committed at the repository root in `.github/workflows/`; it will use `svsbackend/` as the Docker build context.
2. Confirm the exact repository URL/owner and that pushes to `dipak` should publish a production candidate. Decide whether pull requests should run non-publishing CI checks too; pull requests must never receive Docker Hub write credentials.
3. Sign in to Docker Hub and confirm the target namespace (personal username or organization) and repository name. If it does not exist, create `svschool-app` under the intended namespace from **My Hub → Repositories → Create repository** (organization labels can differ). Decide whether it is private or public. In examples below, substitute the exact namespace for `DOCKERHUB_NAMESPACE`.
4. Confirm who is allowed to change workflow files and who can deploy to production. Restrict write/admin access to the people who need it; a workflow change can access any secret granted to its job.
5. In GitHub repository **Settings → Actions → General**, confirm Actions are enabled for the repository and the selected policy permits the workflow. Confirm which person/team owns the production server, how operators connect to it, where its Compose file and protected environment file live, and who can approve releases. Use individual server accounts rather than shared credentials.
6. Record names and locations only in the runbook (for example, “token is in GitHub Actions secret `DOCKERHUB_TOKEN`”). Never record the token, password, private key, database URL/password, or `APP_KEY` itself.

### Step 2 — Prepare Docker configuration for production

1. Change the app service from `build:` and `image: svschool-app:local` to a configurable registry image, such as `${APP_IMAGE:-docker.io/DOCKERHUB_NAMESPACE/svschool-app}:${APP_TAG:-latest}`. Keep a development compose configuration if local builds are still needed.
2. Move production settings out of the committed Compose file. Use an untracked, permission-restricted server `.env` or Docker secrets for `APP_KEY`, database credentials, and other private settings. Set `APP_ENV=production`, `APP_DEBUG=false`, the real `APP_URL`, and production storage/session/cache settings.
3. Avoid publishing database and Redis ports publicly unless the server architecture requires it; allow access only from trusted networks.
4. Choose an explicit migration policy. Prefer having deployment run `php artisan migrate --force` once before switching/restarting the application, and set `RUN_MIGRATIONS=false` for normal app startup to avoid concurrent replicas racing migrations. Ensure the entrypoint can support this policy.
5. After migration, prepare caches using production settings (at minimum config and view cache; route cache only after confirming route definitions are cache-compatible). Clear stale caches before rebuilding them.
6. Add a production health check for `/up` and confirm the app container has a useful health status. Keep `/api/v1/ping` as a secondary API-specific smoke check if appropriate.
7. Resolve the host port/documentation mismatch and make server instructions agree with the actual production listener and reverse-proxy/TLS setup.
8. Review the Docker build context, PHP extensions, file ownership, and Apache document root. Do not bake `.env`, database files, logs, credentials, or runtime uploads into the image.

### Step 3 — Add GitHub Actions CI and Docker Hub publishing

The workflow is [`.github/workflows/svsbackend-docker-publish.yml`](../.github/workflows/svsbackend-docker-publish.yml) at the GitHub repository root; the Laravel build context is `svsbackend/`.

On pushes to `dipak`:

1. Check out the repository and configure Buildx.
2. Run the agreed checks in `svsbackend`, including Composer dependency validation and Laravel tests. Add frontend asset compilation (`npm ci` and `npm run build`). Use a disposable test database/configuration rather than production services or credentials.
3. Build the Docker image using `svsbackend/Dockerfile` and `svsbackend/` as context. Fail before publishing if the build fails.
4. Start a container from the built image with safe test-only settings, wait for readiness, and smoke-test `/up` (and optionally `/api/v1/ping`). Stop and remove the test container even on failure.
5. Only after checks pass, authenticate to Docker Hub and push both:
   - `DOCKERHUB_NAMESPACE/svschool-app:latest`
   - `DOCKERHUB_NAMESPACE/svschool-app:<full-git-commit-sha>`
6. Optionally publish OCI labels for source repository, commit, and build time. Pin third-party Actions to reviewed versions/commit SHAs and grant the workflow only the permissions it needs.
7. Configure workflow concurrency so two pushes do not race while updating `latest`. A failed check must never update Docker Hub tags.

### Step 4 — Configure credentials and access securely

This section describes where to create and save the values. Use the web UI from a trusted, up-to-date device and HTTPS. Do not send secrets to the developer, paste them into chat, commit them, put them in workflow YAML, or capture them in screenshots.

#### 4A. Create a Docker Hub push token for GitHub Actions

1. Sign in at [Docker Hub](https://hub.docker.com/) using an account that can publish to the target namespace/repository.
2. Prefer a dedicated CI service/robot identity or a dedicated token owner over a developer's everyday account. If Docker Hub offers a scoped organization/service-account token for your plan, limit it to this image repository; otherwise create a dedicated PAT and choose the narrowest available scope.
3. In the Docker Hub avatar menu, open **Account settings → Personal access tokens → Generate new token**. Name it clearly, such as `github-actions-svschools-push`, choose an expiry according to the rotation policy, and grant **Read & Write** for publishing (do not grant Delete unless the CI actually needs to delete tags).
4. Generate and copy the token once into a password manager temporarily. Docker Hub only displays the generated value at creation; do not save it in a repo file or terminal history. If the token was exposed, revoke it and create another.
5. Record the Docker Hub username/robot username. The workflow uses this username plus the token; it must not use the Docker Hub account password. Docker's token workflow and permissions are documented in [Docker Hub personal access tokens](https://docs.docker.com/security/access-tokens/personal-access-tokens/).

#### 4B. Save the publishing token in GitHub

1. Open the GitHub repository page. Select **Settings** (if it is hidden in the narrow header, use the repository menu and select Settings).
2. In the left sidebar, open **Secrets and variables → Actions**.
3. For the simplest first setup, on **Repository secrets**, click **New repository secret** and add:
   - `DOCKERHUB_USERNAME` — the Docker Hub service/robot or publishing username. This is not usually secret, but keeping it beside the token simplifies workflow configuration.
   - `DOCKERHUB_TOKEN` — the Docker Hub Read & Write token generated above.
4. Click **Add secret** for each value. GitHub will not show the stored secret later; to rotate it, replace it with a newly generated token. GitHub secrets are encrypted and only injected into jobs that explicitly reference them. Follow [GitHub's instructions for Actions secrets](https://docs.github.com/en/actions/how-tos/write-workflows/choose-what-workflows-do/use-secrets).
5. Better isolation once a production approval gate exists: create a GitHub Environment named `docker-publish` or `production` under **Settings → Environments**, add the token as an environment secret, set allowed deployment branches to `dipak`, and require an authorized reviewer if manual approval is desired. The publishing job must declare that environment to receive those secrets.
6. Keep namespace/image name as non-secret workflow variables or literal configuration. Do not put tokens in repository variables, which are not encrypted secrets.
7. Grant the workflow only the required `GITHUB_TOKEN` permissions (typically read repository contents; no package write permission is needed for Docker Hub). Do not use `pull_request_target` to run untrusted pull-request code with secrets. Never print the secret or enable shell tracing (`set -x`) around login.
8. On **Settings → Secrets and variables → Actions → Variables**, click **New repository variable** and create `DOCKERHUB_NAMESPACE` with the exact Docker Hub username or organization that owns `svschool-app`. This name is public metadata, so it belongs in a variable, not a secret. The workflow exits before login/publishing if this variable is missing.

#### 4C. Keep GitHub access and branch changes controlled

1. The developer pushes code with their own GitHub account and SSH key or HTTPS credential manager. Do not share GitHub passwords, private keys, or personal access tokens. Use a fine-grained GitHub token only if a CLI automation specifically needs one, limited to this repository and necessary permissions.
2. In repository **Settings → Branches** (or **Rules → Rulesets**, depending on the UI), add a protection rule/ruleset for `dipak` if the team wants a reviewed release branch. Require pull-request review/status checks as team policy dictates, restrict force pushes/deletions, and limit who can bypass the rule.
3. If direct push to `dipak` is intentionally retained for the first workflow, still protect the workflow files with review/access rules where practical. Keep the build/push workflow restricted to `push` events on `dipak`; use a separate unprivileged test job for pull requests.
4. Give Actions secret administration only to repository administrators/trusted owners. Review collaborators and Actions settings periodically. A contributor who can change a workflow on a secret-enabled branch may be able to exfiltrate that secret.

#### 4D. Prepare secure server access and Docker Hub pull access

1. Use SSH to administer the Linux server. SSH encrypts the connection between the operator workstation and server; Docker CLI communicates with Docker Hub over HTTPS/TLS. Do not enable an unauthenticated remote Docker TCP socket.
2. Ask the server administrator to provision a named, non-root deploy/operator account. Add that operator's **public** SSH key to the account's `~/.ssh/authorized_keys`; keep the **private** key only on the operator's secured workstation/password manager. Do not send the private key in email/chat or store it in this repository.
3. On first SSH connection, verify the host key fingerprint with the server administrator through a separate trusted channel before accepting it. Do not disable host-key checking to “make SSH work.” Keep server packages, OpenSSH, and Docker updated; use key-based auth, disable password SSH where operationally possible, and restrict SSH with firewall/VPN/allowlisted IPs.
4. On Docker Hub, create a second token for the server, named e.g. `production-server-pull`, with **Read-only** access to the image/repository. Do not reuse the GitHub Actions Read & Write token. If the registry plan supports repository-scoped/organization access tokens, restrict it to this image.
5. SSH into the server and log in as the named deploy account. Run `docker login --username DOCKERHUB_USERNAME` and enter/paste the read-only token only at the hidden password prompt (or pipe it from a secrets manager to `--password-stdin`). Do not put the token directly in a command argument: command arguments may appear in process listings or shell history.
6. Docker stores login credentials under the deploy user's Docker config (commonly `~/.docker/config.json`). Protect the account/home directory and back up the token in the approved secrets manager. If the server supports a Docker credential helper/secret store, configure that. Do not bake this login file into an image or copy it into the Git checkout.
7. Server database credentials and Laravel `APP_KEY` belong in the server-side protected environment file or a host secrets manager, not GitHub Actions: for an operator-run deployment the CI system does not need production database access. Keep the production environment file outside the build context/repository, set ownership to the deploy/runtime account as needed, and use restrictive file permissions such as `chmod 600` with the appropriate owner. Never upload it as an Actions artifact or Docker build argument.
8. Use HTTPS for the live site through the existing or planned reverse proxy and valid certificate. Keep MySQL and Redis private to the server/private network. Only the web listener should be reachable publicly. Rotate exposed tokens immediately and remove stale SSH keys/accounts.

#### 4E. Optional: automatic SSH deployment from Actions (not required initially)

The requested initial flow has an operator pull the image on the server, so GitHub does not need server credentials. If automation is approved later:

1. Create a dedicated server deploy account/key pair used only by this repository's deployment workflow; do not reuse a human's SSH key.
2. Restrict the server key to the deployment account and deployment directory/commands as far as practical. The workflow should be limited to the protected `production` environment, only run after successful image publication, and require an approval if that is the release policy.
3. Save the private key, server hostname, SSH username, and verified known-host key/fingerprint as `production` environment secrets/variables in **Settings → Environments → production**. Save the host fingerprint through an out-of-band verification process, not by blindly trusting `ssh-keyscan` output during each workflow run.
4. Use an SSH action/client pinned to an immutable reviewed version, strict host-key checking, minimal `GITHUB_TOKEN` permissions, and no secret access in pull-request jobs. Do not copy the server `.env` or Docker Hub pull token back to GitHub.
5. Prefer letting the server pull the image with its own read-only Docker Hub token. The workflow should request deployment of an image SHA tag; the server should never build production images from source.

The Docker Hub namespace and image name can be workflow variables rather than secrets. If automatic SSH deployment is introduced later, add a dedicated restricted server SSH private key, known-host fingerprint, and server/deploy path as protected environment secrets/variables; do not add these for the operator-run initial flow. Rotate tokens if exposed, and document who owns renewal. Never print secret values in Actions logs.

### Step 5 — Publish and verify the first image

1. Push the workflow and required Docker/Compose changes to `dipak` using the developer's own GitHub login.
2. In GitHub, open the repository and select **Actions**. Open the run started by the push. Check each job/step: tests, frontend build, Docker build, container smoke test, Docker Hub login, and image push. A red/failed run means do not deploy; fix and push again.
3. In Docker Hub, select the namespace, open the `svschool-app` repository, then open **Tags**. Confirm `latest` and the full commit-SHA tag were updated by that successful run and have the expected commit metadata.
4. If the Docker Hub repository is private, confirm the server's read-only login can pull the image. Do not make the repository public just to avoid configuring server access.
5. On the server, pull the SHA-tagged image first. Confirm architecture, image size, and that it starts with production environment values. Keep the prior deployed tag available until production verification completes.

### Step 6 — Deploy on the server

1. Record the currently running image tag and confirm a database backup/recovery point before applying migrations.
2. Pull the exact SHA tag from Docker Hub. `latest` can be pulled for convenience, but resolve and record its SHA before deployment so the release is reproducible.
3. Run the migration command once against the production database (`php artisan migrate --force`) using the new image and production environment. Review migration behavior for backward compatibility and data impact before rollout.
4. Build Laravel production caches using the new image and production settings; clear stale caches first. Do not run `db:seed` as part of routine deployment.
5. Update the server's selected image tag and restart/recreate the app service. Keep database and uploads/storage volumes persistent.
6. Wait for the container health check. Check logs for startup errors, migration output, permission problems, and failed external connections.

Exact commands will be finalized when the production Compose file and server paths are confirmed; do not copy the local development Compose credentials into production.

The eventual operator runbook should show commands in this order, with actual host/path/image substituted and secrets omitted from the command text:

```sh
# Connect from the approved workstation using the named operator account.
ssh deploy@SERVER_HOST

# Change to the server's production deployment directory.
cd /opt/svschool

# Pull the exact immutable image tag. Docker login has already been set up
# for this server account using a read-only token.
APP_TAG=FULL_COMMIT_SHA docker compose --env-file /etc/svschool/compose.env pull app

# Run migrations once using the new image before restarting the web service.
# Compose config sets RUN_MIGRATIONS=false for normal container startup.
APP_TAG=FULL_COMMIT_SHA docker compose --env-file /etc/svschool/compose.env run --rm --no-deps app php artisan migrate --force

# Recreate the running app using the selected image tag.
APP_TAG=FULL_COMMIT_SHA docker compose --env-file /etc/svschool/compose.env up -d --no-build app

# Rebuild intended Laravel caches in the running production container.
docker compose --env-file /etc/svschool/compose.env exec -T app php artisan config:clear
docker compose --env-file /etc/svschool/compose.env exec -T app php artisan view:clear
docker compose --env-file /etc/svschool/compose.env exec -T app php artisan config:cache
docker compose --env-file /etc/svschool/compose.env exec -T app php artisan view:cache

# Check state/logs and the HTTPS health endpoint from the server or a trusted client.
docker compose --env-file /etc/svschool/compose.env ps
docker compose --env-file /etc/svschool/compose.env logs --tail=100 app
curl --fail --silent --show-error https://YOUR_DOMAIN/up
```

These are a runbook template, not commands to run against production before the production Compose service name, image variable, environment file, and reverse proxy are finalized. The migration invocation uses Compose's `run` to apply the same service environment and image without replacing the existing app container. If the Dockerfile entrypoint's startup hooks change, verify that this one-off command still invokes exactly the expected artisan command. Add `route:cache` only after verifying it against the app's routes. Keep the database backup and old image reference available while doing this.

To roll back the application image, select the previously recorded `FULL_COMMIT_SHA` and run `docker compose ... up -d --no-build app`, then repeat health checks. Do not automatically reverse database migrations; restore schema/data only through the approved recovery procedure.

### Step 7 — Sanity test, go live, and rollback

1. Check the public HTTPS home page and `/up`; verify expected response status and no debug/error page.
2. Check `/api/v1/ping` if the public API is in scope. Verify login and one key authenticated workflow using a non-admin test account.
3. Confirm static assets load, database-backed screens work, logs show no new errors, and background/Redis integrations behave as expected.
4. Monitor application and web-server logs after switching traffic. Announce the release as live only after checks pass.
5. If checks fail, switch the configured image tag back to the previous SHA, restart the app, and repeat health/smoke checks. Database rollback is not automatic: use backward-compatible migrations and a tested backup/restore plan for schema/data recovery.

## Completion criteria

- A push to `dipak` cannot publish unless CI checks, image build, and container smoke test pass.
- Docker Hub has both a `latest` tag and a matching immutable commit-SHA tag for each successful build.
- No credentials are stored in Git, image layers, or workflow logs.
- The server can deploy a selected SHA tag, run migrations and cache preparation, perform health/sanity checks, and restore the prior image tag.
- Production configuration is separate from the local development Compose configuration and has documented ownership/backup/rollback procedures.

## Decisions required before implementation

- Exact GitHub repository and Docker Hub namespace/repository.
- Whether the Docker Hub image is public or private.
- Production server hostname/access owner, deployment directory, reverse proxy/TLS details, and production Compose file location.
- Whether first deployments remain operator-run or should later be triggered automatically after image publication.
- Which Laravel feature tests and user-facing flows are reliable release gates.
- Database backup/restore method and acceptable maintenance window for production migrations.

## Operator checklist: enter credentials without exposing them

- [ ] Create separate Docker Hub credentials for CI push and server pull; CI token has only publish access, server token only pull access.
- [ ] Put CI values only in GitHub Actions **Secrets** (`DOCKERHUB_USERNAME`, `DOCKERHUB_TOKEN`) at the repository or `docker-publish` environment level.
- [ ] Do not add production DB credentials, `APP_KEY`, server `.env`, or server SSH private key to CI for the operator-run deployment flow.
- [ ] Provision a named SSH operator account, verify server host key, and store only the Docker Hub read token in the server's credential store.
- [ ] Keep the server `.env` out of Git and image build context, with restricted permissions and a backup/rotation owner.
- [ ] Confirm no workflow logs, shell history, Docker build args, screenshots, chat messages, or committed files contain secret values.
- [ ] After a successful publish, test server pull/deployment with an immutable SHA tag and keep the prior SHA available for rollback.
