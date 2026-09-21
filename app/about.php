<?php
require __DIR__ . '/ui.php';

// Whitelisted env vars only — never dump the full environment in a demo app.
$show = ['NAMESPACE', 'HOSTNAME', 'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_REQUIRED'];

page_top('About', 'about.php');
?>
<div class="card">
  <h2>Runtime</h2>
  <table>
    <tr><th>Setting</th><th>Value</th></tr>
    <tr><td>PHP version</td><td><code><?= PHP_VERSION ?></code></td></tr>
    <tr><td>pdo_sqlsrv</td><td><?= extension_loaded('pdo_sqlsrv') ? '<span class="ok">loaded</span>' : '<span class="err">missing</span>' ?></td></tr>
    <tr><td>Server API</td><td><code><?= PHP_SAPI ?></code></td></tr>
    <tr><td>OS</td><td><code><?= htmlspecialchars(php_uname('s') . ' ' . php_uname('r')) ?></code></td></tr>
  </table>
</div>

<div class="card">
  <h2>Configuration (injected by OpenShift)</h2>
  <table>
    <tr><th>Env var</th><th>Value</th></tr>
    <?php foreach ($show as $k): $v = getenv($k); ?>
    <tr><td><code><?= $k ?></code></td><td><?= $v === false ? '<span class="warn">not set</span>' : '<code>' . htmlspecialchars($v) . '</code>' ?></td></tr>
    <?php endforeach; ?>
    <tr><td><code>DB_PASSWORD</code></td><td><?= getenv('DB_PASSWORD') === false ? '<span class="warn">not set</span>' : '<span class="ok">set</span> (masked — mounted from Secret <code>php-app-db</code>)' ?></td></tr>
  </table>
  <p>Everything above arrives via the Deployment's env — the <code>DB_*</code> values from a Secret (Module 1.8), the namespace from the Downward API. Same image, any environment; config lives in the platform, not the code.</p>
</div>
<?php page_bottom(); ?>
