# 1.4 — Connecting to External Image Registries

Two distinct needs: **pulling** images from a private registry (Quay, ACR, Artifactory, MCR) and **pushing** build output to one.

## Pull secret for the project

```bash
oc create secret docker-registry quay-pull \
  --docker-server=quay.io \
  --docker-username=<user-or-robot> \
  --docker-password=<token> \
  -n getting-started
```

Link it so all pods in the project can use it at pull time:

```bash
oc secrets link default quay-pull --for=pull -n getting-started
```

> The `default` service account runs your pods; `builder` runs builds. Link the secret to `builder` too (with `--for=pull,mount`) if a build's base image lives in the private registry.

## Cluster-wide pull secret

If every project needs the registry (common for an enterprise Artifactory), append it to the global pull secret instead:

```bash
oc get secret pull-secret -n openshift-config -o jsonpath='{.data.\.dockerconfigjson}' | base64 -d > pull-secret.json
# merge your registry auth into pull-secret.json, then:
oc set data secret/pull-secret -n openshift-config --from-file=.dockerconfigjson=pull-secret.json
```

Nodes roll the change out automatically — no reboot needed.

## Import an external image as an ImageStream

ImageStreams give you a stable internal reference plus change tracking:

```bash
oc import-image mssql-server:2022 \
  --from=mcr.microsoft.com/mssql/server:2022-latest \
  --confirm --scheduled -n getting-started
```

- `--scheduled` re-checks the source ~every 15 min and updates the tag when the upstream image changes.
- Reference it in workloads as `image-registry.openshift-image-registry.svc:5000/getting-started/mssql-server:2022` or let a trigger do it.

## Push build output to an external registry

By default builds push to the internal registry. To push to Quay/ACR instead, set the build output and a push secret:

```bash
oc create secret docker-registry quay-push --docker-server=quay.io \
  --docker-username=<robot> --docker-password=<token> -n getting-started

oc patch bc/php-app -n getting-started --type merge -p '{
  "spec": {"output": {
    "to": {"kind": "DockerImage", "name": "quay.io/<org>/php-app:latest"},
    "pushSecret": {"name": "quay-push"}
  }}}'
```

## Console path

**Workloads → Secrets → Create → Image pull secret**, then link via the service account, or set it directly in the Import from Git advanced options.
