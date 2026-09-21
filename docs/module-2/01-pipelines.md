# 2.1 — CI with OpenShift Pipelines (Tekton)

Module 1's webhook-triggered BuildConfig is fine for day one. Real delivery wants a pipeline: clone → build → push → deploy, as declarative YAML, running in pods, with no central CI server to maintain.

## Install

**Operators → OperatorHub → Red Hat OpenShift Pipelines → Install** (defaults are fine, all namespaces). CLI users: install `tkn` from the console's Command Line Tools page.

## The pipeline

[`pipelines/pipeline.yaml`](../../pipelines/pipeline.yaml) defines three tasks using the cluster resolver (tasks ship with the operator in `openshift-pipelines` — no ClusterTask deprecation issues):

1. **git-clone** — fetch this repo into a shared workspace
2. **buildah** — build the Dockerfile, push to the internal registry
3. **deploy** — `oc rollout restart` so the Deployment picks up the new image

```bash
oc apply -f pipelines/pipeline.yaml -n getting-started
oc apply -f pipelines/pvc.yaml -n getting-started       # workspace volume
```

## Run it

```bash
tkn pipeline start php-app-pipeline \
  -n getting-started \
  --param git-url=https://github.com/<your-org>/ocp-getting-started.git \
  --param image=image-registry.openshift-image-registry.svc:5000/getting-started/php-app:latest \
  --workspace name=shared-workspace,claimName=pipeline-workspace \
  --use-param-defaults --showlog
```

**Console:** Pipelines → Pipelines → php-app-pipeline → Actions → Start. The PipelineRun visualization shows each task live — great screen for demos.

## Trigger on push

Add an EventListener + TriggerTemplate so a Git webhook starts the pipeline ([`pipelines/triggers.yaml`](../../pipelines/triggers.yaml)):

```bash
oc apply -f pipelines/triggers.yaml -n getting-started
oc get route el-php-app -n getting-started    # webhook URL for your Git provider
```

Push a commit → watch a PipelineRun appear. That's CI. Deployment, though, still happens *push-style* from the pipeline — Module 2.2 flips that to pull-based GitOps.
