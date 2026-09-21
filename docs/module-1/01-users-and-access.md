# 1.1 — Creating Users (htpasswd) and RBAC Basics

A fresh cluster has only `kubeadmin`. First task: configure an identity provider (IdP) and stop using `kubeadmin` for daily work. htpasswd is the simplest IdP — ideal for labs and POCs. (For production, use your corporate IdP: LDAP, OIDC/Entra ID, etc. — same OAuth CR, different provider block.)

## Create the htpasswd file

```bash
htpasswd -c -B -b users.htpasswd ocpadmin 'ChangeMe-Admin1!'
htpasswd -B -b users.htpasswd developer1 'ChangeMe-Dev1!'
htpasswd -B -b users.htpasswd developer2 'ChangeMe-Dev2!'
htpasswd -B -b users.htpasswd viewer1 'ChangeMe-View1!'
```

- `-c` creates the file (first user only), `-B` uses bcrypt, `-b` takes the password on the command line.

## Load it into the cluster

```bash
oc create secret generic htpass-secret \
  --from-file=htpasswd=users.htpasswd \
  -n openshift-config
```

Apply the OAuth configuration:

```yaml
# oauth-htpasswd.yaml
apiVersion: config.openshift.io/v1
kind: OAuth
metadata:
  name: cluster
spec:
  identityProviders:
  - name: htpasswd_provider
    mappingMethod: claim
    type: HTPasswd
    htpasswd:
      fileData:
        name: htpass-secret
```

```bash
oc apply -f oauth-htpasswd.yaml
```

The OAuth pods in `openshift-authentication` restart (1–2 min). Then log in:

```bash
oc login -u developer1 https://api.<cluster-domain>:6443
```

**Console path:** Administration → Cluster Settings → Configuration → OAuth → Identity providers → Add → HTPasswd (upload the file directly — the console creates the secret for you).

## Grant roles

Users exist in OpenShift only after their first login. Grant `cluster-admin` to your admin user:

```bash
oc adm policy add-cluster-role-to-user cluster-admin ocpadmin
```

Project-scoped roles (the ones you'll use most):

```bash
oc adm policy add-role-to-user admin developer1 -n getting-started   # manage the project incl. RBAC
oc adm policy add-role-to-user edit  developer2 -n getting-started   # create/modify workloads, no RBAC
oc adm policy add-role-to-user view  viewer1    -n getting-started   # read-only
```

| Role | Scope | Typical persona |
|---|---|---|
| `cluster-admin` | cluster | platform team |
| `admin` | project | app team lead |
| `edit` | project | developer |
| `view` | project | auditor / support |

## Add, change, or remove users later

The htpasswd data lives in the secret — edit and re-apply:

```bash
oc get secret htpass-secret -n openshift-config -o jsonpath='{.data.htpasswd}' | base64 -d > users.htpasswd
htpasswd -B -b users.htpasswd developer3 'ChangeMe-Dev3!'    # add/update
htpasswd -D users.htpasswd viewer1                            # delete
oc set data secret/htpass-secret --from-file=htpasswd=users.htpasswd -n openshift-config
```

If you removed a user, also clean up their cluster objects:

```bash
oc delete user viewer1
oc delete identity htpasswd_provider:viewer1
```

## Remove kubeadmin (recommended once your admin works)

```bash
# Verify ocpadmin has cluster-admin FIRST — this is irreversible
oc delete secret kubeadmin -n kube-system
```
