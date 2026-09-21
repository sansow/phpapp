# 1.5 — Deploying the PHP Application from Git

Time to deploy the sample app in this repo. It's a small PHP status dashboard that runs standalone now and connects to SQL Server once you complete [Module 1.8](08-sqlserver-backend.md).

## How the build works

This repo has a `Dockerfile` at the root, so OpenShift's **Import from Git** auto-detects it and uses the **Docker build strategy**. The Dockerfile extends Red Hat's UBI9 PHP S2I image and adds the Microsoft SQL Server drivers (`msodbcsql18`, `pdo_sqlsrv`) — those aren't in the stock PHP builder, which is exactly why the Dockerfile exists.

> Vanilla PHP apps (no SQL Server) don't need a Dockerfile at all: point Import from Git at the repo and the **PHP builder image (S2I)** builds it directly from source. Worth showing customers both paths.

## Console: Import from Git

1. Project `getting-started` selected → click **+ (quick create)** → **Import from Git**
2. **Git Repo URL**: your fork of this repo (add a source secret if private — Module 1.3)
3. Import strategy auto-selects **Dockerfile** — leave it
4. **Application**: `php-demo` · **Name**: `php-app`
5. Resource type: **Deployment** · **Create a route**: checked
6. **Create**

Watch **Workloads → Topology**: the build badge spins, then the pod goes dark blue (running). Click the route arrow icon to open the app — you should see the dashboard with the pod name and "SQL Server: not configured".

The app has three pages plus the probe endpoints: **Dashboard** (`/`), **Visits** (`/visits.php` — reads the visit log from SQL Server after Module 1.8), and **About** (`/about.php` — shows the env vars OpenShift injects, with the DB password masked; a nice visual for the config-from-Secrets story).

## CLI equivalent

```bash
oc new-app https://github.com/<your-org>/ocp-getting-started.git \
  --name=php-app -n getting-started
oc expose svc/php-app -n getting-started
oc logs -f bc/php-app         # follow the build
oc get route php-app          # the app URL
```

## What got created

```bash
oc get bc,build,deploy,svc,route -l app=php-app -n getting-started
```

| Object | Purpose |
|---|---|
| BuildConfig | how to build (Git source → Docker strategy) |
| Build | one execution of the BuildConfig |
| ImageStream | tracks the built image; new image → automatic rollout |
| Deployment | runs the pods |
| Service | stable internal endpoint |
| Route | external URL (edge TLS by default) |

## Trigger a rebuild

Push a commit and re-run, or manually:

```bash
oc start-build php-app -n getting-started --follow
```

For push-triggered builds, add a webhook: `oc describe bc/php-app` shows the webhook URL — add it to your Git repo's webhook settings. (Module 2 replaces this with Pipelines.)

## Scale it

```bash
oc scale deploy/php-app --replicas=3 -n getting-started
```

Refresh the app a few times — the pod name changes as the route load-balances. Instant conversation-starter about statelessness, which sets up Modules 1.7–1.8.
