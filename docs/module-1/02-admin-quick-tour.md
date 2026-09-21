# 1.2 — Administration Quick Tour

## The 4.22 console

Since OpenShift 4.19 the web console uses a **unified perspective** — the separate Developer perspective is disabled by default. Everything lives in one navigation:

- **Workloads → Topology** — the application-centric graph view
- **+ (quick create, top nav)** — Import from Git, container images, YAML
- **Administration** — cluster settings, RBAC, quotas

Prefer the classic Developer perspective? Re-enable it (or follow the built-in quick start under **Help → Quick Starts → Enable Developer Perspective**):

```bash
oc patch console.operator.openshift.io cluster --type merge \
  -p '{"spec":{"customization":{"perspectives":[{"id":"dev","visibility":{"state":"Enabled"}}]}}}'
```

## Projects

A project is a Kubernetes namespace plus annotations and RBAC conveniences. Create the one used throughout this guide:

```bash
oc new-project getting-started --display-name="Getting Started" \
  --description="OCP 4.22 onboarding — PHP + SQL Server sample"
```

Useful project commands:

```bash
oc projects                 # list projects you can see
oc project getting-started  # switch context
oc status                   # summary of what's running
oc get events --sort-by=.lastTimestamp   # recent activity, newest last
```

## Resource quotas and limit ranges

Quotas cap total consumption per project; limit ranges set per-container defaults so pods land under the quota without every dev specifying resources.

```yaml
# quota.yaml
apiVersion: v1
kind: ResourceQuota
metadata:
  name: getting-started-quota
  namespace: getting-started
spec:
  hard:
    requests.cpu: "4"
    requests.memory: 8Gi
    limits.cpu: "8"
    limits.memory: 16Gi
    persistentvolumeclaims: "10"
---
apiVersion: v1
kind: LimitRange
metadata:
  name: getting-started-limits
  namespace: getting-started
spec:
  limits:
  - type: Container
    default:            # applied when a container sets no limits
      cpu: 500m
      memory: 512Mi
    defaultRequest:
      cpu: 100m
      memory: 256Mi
```

```bash
oc apply -f quota.yaml
oc describe quota -n getting-started    # watch usage vs hard limits
```

**Console path:** Administration → ResourceQuotas / LimitRanges.

## Five commands worth memorizing

```bash
oc get clusteroperators                  # cluster health at a glance — all should be Available=True
oc get nodes -o wide                     # node status, versions, IPs
oc adm top pods -n getting-started       # live CPU/memory per pod
oc logs -f deploy/<name>                 # follow logs for a deployment
oc debug node/<node-name>                # troubleshooting shell on a node
```

## Where things are in the console

| Task | Path |
|---|---|
| Cluster health | Home → Overview |
| Update the cluster | Administration → Cluster Settings |
| RBAC | User Management → Roles / RoleBindings |
| Operator installs | Operators → OperatorHub |
| Storage classes | Storage → StorageClasses |
