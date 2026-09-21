# OpenShift 4.22 — Getting Started Guide

A hands-on onboarding kit for teams new to OpenShift Container Platform 4.22. It walks a fresh cluster from first login to a running PHP application backed by SQL Server, with stateful workloads, CI/CD, and GitOps — using this repo itself as the sample application.

## What you'll build

A PHP web app (in [`app/`](app/)) deployed from this Git repo, wired to a SQL Server 2022 backend running on OpenShift, plus a MongoDB StatefulSet to demonstrate stateful workload patterns.

## Modules

### Module 1 — Getting Started
| # | Topic |
|---|-------|
| [1.1](docs/module-1/01-users-and-access.md) | Creating users (htpasswd) and RBAC basics |
| [1.2](docs/module-1/02-admin-quick-tour.md) | Administration quick tour — projects, quotas, the 4.22 console |
| [1.3](docs/module-1/03-connect-git.md) | Connecting to Git repositories (public + private) |
| [1.4](docs/module-1/04-external-registries.md) | Connecting to external image registries |
| [1.5](docs/module-1/05-deploy-php-app.md) | Deploying the PHP application from Git |
| [1.6](docs/module-1/06-health-probes.md) | Health probes — liveness, readiness, startup |
| [1.7](docs/module-1/07-mongodb-statefulset.md) | Stateful workloads — MongoDB StatefulSet |
| [1.8](docs/module-1/08-sqlserver-backend.md) | SQL Server 2022 backend for the PHP app |

### Module 2 — CI/CD & GitOps
| # | Topic |
|---|-------|
| [2.1](docs/module-2/01-pipelines.md) | CI with OpenShift Pipelines (Tekton) |
| [2.2](docs/module-2/02-gitops.md) | CD with OpenShift GitOps (Argo CD) |

### Module 3 — Advanced (outline)
| # | Topic |
|---|-------|
| [3.x](docs/module-3/README.md) | Logging (Loki), Monitoring, Service Mesh |

## Prerequisites

- OpenShift 4.22 cluster with `cluster-admin` access for Module 1.1–1.2 (later modules work as a project admin)
- `oc` CLI matching your cluster version — download from the console: **?** (help menu) → **Command Line Tools**
- `htpasswd` locally (`httpd-tools` on RHEL/Fedora, `brew install httpd` on macOS)
- Fork or clone of this repo pushed somewhere your cluster can reach

## Conventions

- Console paths are given for the **unified console** (default in 4.22). Where the classic Developer perspective differs, it's called out.
- All examples use the project `getting-started`. Replace names/passwords before using anything beyond a sandbox.

> Sample credentials in this guide are placeholders for lab use only. Never use them in a real environment.
