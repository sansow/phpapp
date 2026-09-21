# 1.6 — Health Probes

Without probes, OpenShift only knows a container's process is alive — not that the app inside works. The sample app ships three endpoints for the three probe types:

| Probe | Endpoint | Question it answers | On failure |
|---|---|---|---|
| Liveness | `/healthz.php` | Is the process healthy? | container restarted |
| Readiness | `/readyz.php` | Can it serve traffic *right now*? | removed from Service endpoints (no restart) |
| Startup | `/healthz.php` | Has it finished booting? | holds off the other probes |

`/readyz.php` also checks the SQL Server connection when `DB_REQUIRED=true` — so after Module 1.8 you can demo a pod going NotReady when its database disappears, without the pod restarting.

## Add probes via CLI

```bash
oc set probe deploy/php-app -n getting-started --liveness \
  --get-url=http://:8080/healthz.php --initial-delay-seconds=10 --period-seconds=10

oc set probe deploy/php-app -n getting-started --readiness \
  --get-url=http://:8080/readyz.php --initial-delay-seconds=5 --period-seconds=5 --failure-threshold=3

oc set probe deploy/php-app -n getting-started --startup \
  --get-url=http://:8080/healthz.php --period-seconds=5 --failure-threshold=30
```

Or as YAML (see [`manifests/php-app/deployment.yaml`](../../manifests/php-app/deployment.yaml) for the full context):

```yaml
livenessProbe:
  httpGet: { path: /healthz.php, port: 8080 }
  initialDelaySeconds: 10
  periodSeconds: 10
readinessProbe:
  httpGet: { path: /readyz.php, port: 8080 }
  periodSeconds: 5
  failureThreshold: 3
startupProbe:
  httpGet: { path: /healthz.php, port: 8080 }
  periodSeconds: 5
  failureThreshold: 30     # up to 150s to boot before liveness takes over
```

## Console path

**Workloads → Deployments → php-app → Actions → Edit Health Checks** — same three probes with a form UI. (In Topology: right-click the node → Edit Health Checks.)

## See them work

```bash
oc get pods -n getting-started -w        # watch READY column flip 0/1 → 1/1
oc describe pod -l app=php-app | grep -A3 -E 'Liveness|Readiness'
oc get events -n getting-started --sort-by=.lastTimestamp | grep -i probe
```

Demo idea: `oc rsh` into the pod and `rm /opt/app-root/src/healthz.php` — within ~30s the liveness probe fails three times and OpenShift restarts the container, which restores the file. Self-healing, live.

## Guidance worth repeating to app teams

- Readiness should check **dependencies needed to serve** (DB, cache); liveness should check **only the process itself** — a liveness probe that checks the DB turns a database blip into a restart storm.
- Always set a startup probe for slow-booting apps (Java!) instead of huge liveness initial delays.
