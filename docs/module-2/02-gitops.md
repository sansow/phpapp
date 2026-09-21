# 2.2 — CD with OpenShift GitOps (Argo CD)

GitOps inverts deployment: instead of a pipeline pushing changes to the cluster, **Argo CD continuously pulls the cluster toward what Git declares**. Git becomes the audit log, rollback is `git revert`, and drift gets corrected automatically.

## Install

**Operators → OperatorHub → Red Hat OpenShift GitOps → Install** (defaults). This creates the `openshift-gitops` namespace with an Argo CD instance. Get the console link from the app launcher (grid icon, top right) → **Cluster Argo CD**. Login: `admin` / password from:

```bash
oc extract secret/openshift-gitops-cluster -n openshift-gitops --to=-
```

## Point Argo at this repo

[`gitops/application.yaml`](../../gitops/application.yaml) declares: "the `manifests/` directory of this repo, applied to the `getting-started` namespace, kept in sync automatically."

```bash
# Allow the Argo controller to manage the target namespace
oc adm policy add-role-to-user admin \
  system:serviceaccount:openshift-gitops:openshift-gitops-argocd-application-controller \
  -n getting-started

# Update repoURL in gitops/application.yaml to your fork, then:
oc apply -f gitops/application.yaml
```

Open the Argo CD UI: the `php-demo` application appears, syncs, and turns green (Healthy/Synced) as it applies everything in `manifests/` — the PHP app Deployment/Service/Route, MongoDB, SQL Server.

## The three demos that land

1. **Change via Git**: edit `manifests/php-app/deployment.yaml`, bump `replicas: 2 → 3`, commit, push. Within ~3 min (or hit Refresh) Argo syncs and a third pod appears. Nobody touched `oc`.
2. **Drift correction**: `oc scale deploy/php-app --replicas=1`. With `selfHeal: true`, Argo puts it back within seconds. Cluster reality is not allowed to drift from Git.
3. **Rollback**: `git revert` the replica change, push — cluster follows. Same muscle memory as code review; ops changes now get PRs, approvals, and history.

## CI + CD together

End state to draw on the whiteboard:

```
push → Pipeline (clone → buildah → push image) → updates image tag in Git
                                                     ↓
                                    Argo CD syncs → cluster converges
```

The pipeline's only cluster permission is pushing an image; everything that *runs* is declared in Git. That separation — CI builds, GitOps deploys — is the pattern to leave customers with.
