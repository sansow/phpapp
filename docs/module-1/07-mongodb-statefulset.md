# 1.7 — Stateful Workloads: MongoDB StatefulSet

Deployments treat pods as interchangeable. Databases need the opposite: **stable identity** (predictable pod names/DNS) and **storage that follows the pod**. That's a StatefulSet:

- Pods get ordinal names: `mongodb-0`, `mongodb-1`, …
- Each pod gets **its own PVC** from a `volumeClaimTemplates` block — delete the pod, the PVC (and data) survives and reattaches
- A **headless Service** gives each pod stable DNS: `mongodb-0.mongodb-headless.getting-started.svc.cluster.local`
- Ordered, one-at-a-time rollout

## Deploy

Manifests are in [`manifests/mongodb/`](../../manifests/mongodb/):

```bash
oc apply -f manifests/mongodb/ -n getting-started
oc get pods -l app=mongodb -w
```

What's in there:

- `secret.yaml` — root username/password (change it)
- `service.yaml` — the headless Service (`clusterIP: None`)
- `statefulset.yaml` — 1 replica, `volumeClaimTemplates` requesting 5Gi

## Verify identity + persistence

```bash
oc get pvc -n getting-started              # data-mongodb-0 — created by the template
oc rsh mongodb-0
```

Inside the pod:

```bash
mongosh -u admin -p "$MONGO_INITDB_ROOT_PASSWORD"
> use demo
> db.visits.insertOne({store: "GetGo-1234", ts: new Date()})
> db.visits.find()
> exit
exit
```

Now the money demo — kill the pod and prove the data survives:

```bash
oc delete pod mongodb-0
oc get pods -w                             # mongodb-0 comes back with the SAME name
oc rsh mongodb-0 mongosh -u admin -p '<password>' --eval 'db.getSiblingDB("demo").visits.find()'
```

Same name, same PVC, data intact. Contrast with a Deployment pod, which would come back with a new random name and (without a PVC) empty storage.

## Storage notes

- `volumeClaimTemplates` uses the cluster's **default StorageClass** — check with `oc get sc`. Set `storageClassName` explicitly for a specific backend (e.g. ODF `ocs-storagecluster-ceph-rbd`).
- Scaling: `oc scale sts/mongodb --replicas=3` creates `mongodb-1`/`mongodb-2` each with their own PVC — but a real MongoDB **replica set** needs replication config on top. For production Mongo, point customers at an operator; this module is about the StatefulSet primitive.
- Deleting the StatefulSet does **not** delete PVCs — that's a feature. Clean up explicitly: `oc delete pvc -l app=mongodb`.

## Troubleshooting

- **CrashLoopBackOff with permission errors on `/data/db`**: OpenShift runs containers with a random UID under the `restricted-v2` SCC; the pod's `fsGroup` (set automatically) should make the PVC writable. If your storage backend doesn't honor fsGroup, either use a CSI driver that does, or as a lab fallback run with a dedicated SA: `oc create sa mongodb-sa && oc adm policy add-scc-to-user anyuid -z mongodb-sa` and add `serviceAccountName: mongodb-sa` to the pod spec. Discuss the security trade-off when you do.
- **Pending PVC**: no default StorageClass — `oc get sc`, then set one.
