<?php
require __DIR__ . '/ui.php';
require __DIR__ . '/lib/Parsedown.php';

/**
 * The workshop guide, rendered from the repo's docs/ directory.
 * Locally: ../docs relative to app/. In the image: /opt/app-root/docs
 * (same relative position — see the Dockerfile COPY).
 */
const DOCS_DIR = __DIR__ . '/../docs';

/** Ordered manifest — also drives prev/next navigation. */
function guide_manifest(): array {
    return [
        'module-1/01-users-and-access'    => ['1.1', 'Creating Users (htpasswd) & RBAC'],
        'module-1/02-admin-quick-tour'    => ['1.2', 'Administration Quick Tour'],
        'module-1/03-connect-git'         => ['1.3', 'Connecting to Git Repositories'],
        'module-1/04-external-registries' => ['1.4', 'External Image Registries'],
        'module-1/05-deploy-php-app'      => ['1.5', 'Deploying This PHP App from Git'],
        'module-1/06-health-probes'       => ['1.6', 'Health Probes'],
        'module-1/07-mongodb-statefulset' => ['1.7', 'Stateful Workloads: MongoDB'],
        'module-1/08-sqlserver-backend'   => ['1.8', 'SQL Server Backend'],
        'module-2/01-pipelines'           => ['2.1', 'CI with OpenShift Pipelines'],
        'module-2/02-gitops'              => ['2.2', 'CD with OpenShift GitOps'],
        'module-3/README'                 => ['3',   'Advanced Topics (Outline)'],
    ];
}

function repo_url(): string {
    return rtrim(getenv('REPO_URL') ?: '', '/');
}

/** Resolve + validate a doc id against the manifest (no path tricks possible). */
$manifest = guide_manifest();
$docId = $_GET['doc'] ?? null;
$doc = ($docId !== null && isset($manifest[$docId])) ? $docId : null;

if ($doc === null) {
    // ---------- Guide index ----------
    page_top('Guide', 'guide.php');
    ?>
    <div class="card">
      <h2>How to use this guide</h2>
      <p>Work through the modules in order on your own cluster — every command is copy-paste ready
         (hover a code block for the copy button). This very app is the sample workload: you'll deploy
         it in Module 1.5, probe it in 1.6, and give it a database in 1.8.</p>
      <?php if (repo_url()): ?>
        <p>Source repo: <a href="<?= htmlspecialchars(repo_url()) ?>"><code><?= htmlspecialchars(repo_url()) ?></code></a></p>
      <?php endif; ?>
    </div>
    <?php
    $groups = ['module-1' => 'Module 1 — Getting Started',
               'module-2' => 'Module 2 — CI/CD & GitOps',
               'module-3' => 'Module 3 — Advanced'];
    foreach ($groups as $prefix => $label): ?>
      <div class="card">
        <h2><?= htmlspecialchars($label) ?></h2>
        <table>
        <?php foreach ($manifest as $id => [$num, $title]): if (strpos($id, $prefix) !== 0) continue; ?>
          <tr><td style="width:3.5rem"><strong><?= htmlspecialchars($num) ?></strong></td>
              <td><a href="/guide.php?doc=<?= urlencode($id) ?>"><?= htmlspecialchars($title) ?></a></td></tr>
        <?php endforeach; ?>
        </table>
      </div>
    <?php endforeach;
    page_bottom();
    exit;
}

// ---------- Single doc ----------
$file = DOCS_DIR . '/' . $doc . '.md';
$real = realpath($file);
if ($real === false || strpos($real, realpath(DOCS_DIR)) !== 0) {
    http_response_code(404);
    page_top('Not found', 'guide.php');
    echo '<div class="card"><p>Doc not found.</p></div>';
    page_bottom();
    exit;
}

$md = file_get_contents($real);
$pd = new Parsedown();
$pd->setSafeMode(false);          // our own docs; allows inline HTML in them
$html = $pd->text($md);

// Rewrite links: *.md relative links -> guide pages; repo paths -> REPO_URL or plain text
$html = preg_replace_callback('/href="([^"]+)"/', function ($m) use ($doc, $manifest) {
    $href = $m[1];
    if (preg_match('#^(https?:|mailto:|\#)#', $href)) return $m[0];
    $base = dirname($doc);
    $target = $href;
    // normalize relative path against current doc dir
    $parts = [];
    foreach (explode('/', ($base === '.' ? '' : $base . '/') . $target) as $seg) {
        if ($seg === '' || $seg === '.') continue;
        if ($seg === '..') { array_pop($parts); continue; }
        $parts[] = $seg;
    }
    $norm = implode('/', $parts);
    if (substr($norm, -3) === '.md') {
        $id = substr($norm, 0, -3);
        if (isset($manifest[$id])) return 'href="/guide.php?doc=' . urlencode($id) . '"';
    }
    // non-doc repo file (manifests/, pipelines/...) -> link to the repo if configured
    if (repo_url()) return 'href="' . htmlspecialchars(repo_url() . '/blob/main/' . $norm) . '"';
    return 'href="#" onclick="return false" title="Set REPO_URL env var to link repo files"';
}, $html);

[$num, $title] = $manifest[$doc];
$ids = array_keys($manifest);
$pos = array_search($doc, $ids, true);
$prev = $pos > 0 ? $ids[$pos - 1] : null;
$next = $pos < count($ids) - 1 ? $ids[$pos + 1] : null;

page_top("$num $title", 'guide.php');
?>
<div class="card guide-doc"><?= $html ?></div>
<div class="card" style="display:flex; justify-content:space-between; gap:1rem">
  <span><?php if ($prev): ?><a href="/guide.php?doc=<?= urlencode($prev) ?>">&larr; <?= htmlspecialchars($manifest[$prev][0] . ' ' . $manifest[$prev][1]) ?></a><?php endif; ?></span>
  <span><a href="/guide.php">Guide index</a></span>
  <span><?php if ($next): ?><a href="/guide.php?doc=<?= urlencode($next) ?>"><?= htmlspecialchars($manifest[$next][0] . ' ' . $manifest[$next][1]) ?> &rarr;</a><?php endif; ?></span>
</div>
<script>
// copy-to-clipboard on every code block
document.querySelectorAll('.guide-doc pre').forEach(pre => {
  const btn = document.createElement('button');
  btn.textContent = 'Copy';
  btn.className = 'copy-btn';
  btn.onclick = () => {
    navigator.clipboard.writeText(pre.querySelector('code')?.innerText ?? pre.innerText)
      .then(() => { btn.textContent = 'Copied!'; setTimeout(() => btn.textContent = 'Copy', 1500); });
  };
  pre.style.position = 'relative';
  pre.appendChild(btn);
});
</script>
<?php page_bottom(); ?>
