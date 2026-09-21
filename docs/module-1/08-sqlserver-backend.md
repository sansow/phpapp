# 1.8 — SQL Server 2022 Backend for the PHP App

Now give the PHP app a real backend. Microsoft ships SQL Server 2022 as a Linux container (`mcr.microsoft.com/mssql/server`) — it runs well on OpenShift with one caveat covered below.

## The SCC caveat, up front

The mssql image expects to run as its built-in `mssql` user (UID 10001). OpenShift's default `restricted-v2` SCC assigns a random UID instead, which SQL Server doesn't tolerate. The lab-friendly fix: a dedicated service account allowed to run as its declared UID.

```bash
oc create sa mssql-sa -n getting-started
oc adm policy add-scc-to-user anyuid -z mssql-sa -n getting-started
```

> Talking point for customers: this is OpenShift's security posture working as designed — arbitrary images don't get arbitrary UIDs by default; you grant exceptions deliberately, per workload, and they're auditable (`oc get rolebindings -n getting-started`).

## Deploy

Manifests in [`manifests/sqlserver/`](../../manifests/sqlserver/):

```bash
# 1. Edit manifests/sqlserver/secret.yaml — set a strong SA password
#    (SQL Server enforces complexity: 8+ chars, upper+lower+digit/symbol)
oc apply -f manifests/sqlserver/ -n getting-started
oc get pods -l app=mssql -w              # ~60–90s to Ready on first start
oc logs deploy/mssql | grep -i 'ready for client connections'
```

What's in there: `secret.yaml` (SA password), `pvc.yaml` (10Gi at `/var/opt/mssql`), `deployment.yaml` (Recreate strategy — one writer per data dir), `service.yaml` (`mssql:1433`).

## Create the app database

```bash
oc rsh deploy/mssql /opt/mssql-tools18/bin/sqlcmd \
  -S localhost -U sa -P "$MSSQL_SA_PASSWORD" -C \
  -Q "CREATE DATABASE phpdemo"
```

## Wire the PHP app to it

The app reads standard `DB_*` env vars. Feed them from a secret:

```bash
oc create secret generic php-app-db -n getting-started \
  --from-literal=DB_HOST=mssql \
  --from-literal=DB_PORT=1433 \
  --from-literal=DB_NAME=phpdemo \
  --from-literal=DB_USER=sa \
  --from-literal=DB_PASSWORD='<the-sa-password>'

oc set env deploy/php-app --from=secret/php-app-db -n getting-started
oc set env deploy/php-app DB_REQUIRED=true -n getting-started   # readiness now gates on the DB
```

The Deployment rolls automatically. Reload the app: the dashboard flips to **SQL Server: connected**, creates a `visits` table, and shows a live visit counter — proof of app → service → database inside the cluster.

> Note `DB_HOST=mssql` — plain Service DNS. No IPs, no config files; the app finds its database by name. That's the platform doing service discovery.

## Demo the readiness gate

```bash
oc scale deploy/mssql --replicas=0 -n getting-started
oc get pods -l app=php-app -w        # php-app pods go 0/1 NotReady — no restarts
oc scale deploy/mssql --replicas=1 -n getting-started
```

The route returns 503 while nothing is Ready, then recovers on its own. Liveness stayed green throughout — exactly the probe separation from Module 1.6.

## Production notes (say these out loud)

- Use SQL Server **Developer** edition (`MSSQL_PID=Developer`) for labs only; production needs licensing (`MSSQL_PID=Enterprise` etc.).
- Create an app login instead of using `sa` beyond the lab.
- Backups: `/var/opt/mssql` PVC snapshots via CSI, or native SQL backups to object storage.
- For HA, look at SQL Server Availability Groups or keep the DB external and treat OpenShift as the app tier — both are valid patterns.
