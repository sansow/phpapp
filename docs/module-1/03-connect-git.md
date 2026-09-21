# 1.3 — Connecting to Git Repositories

OpenShift builds pull source directly from Git. Public repos need nothing; private repos need a **source secret** in the project.

## Private repo with a token (HTTPS + PAT)

```bash
oc create secret generic git-credentials \
  --from-literal=username=<git-username> \
  --from-literal=password=<personal-access-token> \
  --type=kubernetes.io/basic-auth \
  -n getting-started
```

Tell OpenShift which repos this secret matches, so builds pick it up automatically:

```bash
oc annotate secret git-credentials -n getting-started \
  'build.openshift.io/source-secret-match-uri-1=https://github.com/<your-org>/*'
```

Link it to the `builder` service account (the SA that runs builds):

```bash
oc secrets link builder git-credentials -n getting-started
```

## Private repo over SSH

```bash
oc create secret generic git-ssh-key \
  --from-file=ssh-privatekey=$HOME/.ssh/id_ed25519 \
  --type=kubernetes.io/ssh-auth \
  -n getting-started
oc secrets link builder git-ssh-key -n getting-started
```

Use the `git@github.com:org/repo.git` URL form when creating the app.

## Console path

**+ → Import from Git → Show advanced Git options → Source Secret** — pick an existing secret or create one inline (Basic auth or SSH). Advanced options also let you set **Git reference** (branch/tag/commit) and **Context dir** (subdirectory containing the app).

## Verify

```bash
oc get secrets -n getting-started | grep -E 'basic-auth|ssh-auth'
oc describe sa builder -n getting-started      # secret should appear under Mounted secrets
```

If a build fails with authentication errors, check `oc logs bc/<name>` — the clone step failure appears in the first lines.

## Self-signed / internal Git servers

For an internal GitLab/Gitea with a private CA, add the CA to the secret:

```bash
oc create secret generic git-credentials \
  --from-literal=username=<user> --from-literal=password=<token> \
  --from-file=ca.crt=internal-ca.crt \
  --type=kubernetes.io/basic-auth -n getting-started
```
