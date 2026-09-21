# Module 3 — Advanced Topics (Outline)

Deliberately an outline: run these as follow-on sessions once Modules 1–2 have landed. Each is an operator install plus a focused demo on the same `getting-started` project, so the sample app keeps doing the demo work.

## 3.1 Logging (Loki-based cluster logging)

- Install **Loki Operator** + **Cluster Observability Operator** / logging stack; object storage bucket as the Loki backend (ODF NooBaa works for labs)
- ClusterLogForwarder to collect application + infrastructure logs
- Demo: filter the PHP app's logs in the console (Observe → Logs) by namespace/pod; show a SQL connection error surfacing in one query
- Talking points: log retention/tenancy, forwarding to Splunk/Elastic if the customer has one

## 3.2 Monitoring & Alerting

- Built-in Prometheus already scrapes the platform — enable **user workload monitoring** (one ConfigMap flag) to scrape app metrics
- Add a ServiceMonitor for the PHP app; expose a metric (request count) and graph it in Observe → Metrics
- Create a PrometheusRule alert (e.g., readiness failures > N) and route it via Alertmanager
- Demo: re-run the "scale mssql to 0" scenario from Module 1.8 and watch the alert fire

## 3.3 Service Mesh (OpenShift Service Mesh 3 / Istio)

- Install Service Mesh 3 operator; add `getting-started` to the mesh with sidecar injection
- mTLS between php-app and mssql with zero app changes — the security story
- Traffic management demo: deploy php-app v2, shift 10% of traffic via VirtualService (canary)
- Observability: Kiali graph of app → database call flow

## Suggested sequencing

Monitoring first (fastest win, zero app changes) → Logging → Mesh (biggest lift, save for a dedicated session).
