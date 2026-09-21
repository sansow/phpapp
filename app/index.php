<?php
require __DIR__ . '/db.php';

$pod     = gethostname();
$phpVer  = PHP_VERSION;
[$pdo, $dbStatus] = db_connect();
$visits  = null;
$dbError = null;

if ($pdo !== null) {
    try { $visits = db_record_visit($pdo); }
    catch (Throwable $e) { $dbError = $e->getMessage(); }
}
$connected = $pdo !== null && $dbError === null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>OpenShift Getting Started — PHP Demo</title>
<style>
  :root { color-scheme: light dark; }
  body { font-family: "Red Hat Text", system-ui, sans-serif; margin: 0;
         background: #f2f2f2; color: #151515; }
  @media (prefers-color-scheme: dark) { body { background:#1b1b1d; color:#eee; } .card{background:#26262a!important;} }
  header { background: #ee0000; color: #fff; padding: 1.2rem 2rem; }
  header h1 { margin: 0; font-size: 1.3rem; font-weight: 500; }
  main { max-width: 720px; margin: 2rem auto; padding: 0 1rem; }
  .card { background: #fff; border-radius: 8px; padding: 1.2rem 1.5rem;
          margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.15); }
  .card h2 { margin: 0 0 .6rem; font-size: 1rem; text-transform: uppercase;
             letter-spacing: .05em; opacity: .7; }
  .big { font-size: 1.6rem; font-weight: 600; }
  .ok { color: #3e8635; } .warn { color: #f0ab00; } .err { color: #c9190b; }
  code { background: rgba(128,128,128,.15); padding: .1em .35em; border-radius: 4px; }
  footer { text-align: center; font-size: .8rem; opacity: .6; margin: 2rem 0; }
</style>
</head>
<body>
<header><h1>OpenShift 4.22 Getting Started — PHP Demo</h1></header>
<main>
  <div class="card">
    <h2>Serving pod</h2>
    <div class="big"><?= htmlspecialchars($pod) ?></div>
    <p>Scale the deployment and refresh — this name changes as the Route load-balances across replicas.</p>
  </div>

  <div class="card">
    <h2>SQL Server backend</h2>
    <?php if ($connected): ?>
      <div class="big ok">connected</div>
      <p>Total visits recorded in <code>dbo.visits</code>: <strong><?= (int) $visits ?></strong></p>
      <p>Found via Service DNS: <code><?= htmlspecialchars(getenv('DB_HOST')) ?>:<?= htmlspecialchars(getenv('DB_PORT') ?: '1433') ?></code></p>
    <?php elseif ($pdo !== null && $dbError !== null): ?>
      <div class="big err">query failed</div>
      <p><code><?= htmlspecialchars($dbError) ?></code></p>
    <?php else: ?>
      <div class="big warn"><?= htmlspecialchars($dbStatus) ?></div>
      <p>Complete <strong>Module 1.8</strong> to deploy SQL Server and inject <code>DB_*</code> credentials from a Secret.</p>
    <?php endif; ?>
  </div>

  <div class="card">
    <h2>Runtime</h2>
    <p>PHP <?= htmlspecialchars($phpVer) ?> ·
       pdo_sqlsrv <?= extension_loaded('pdo_sqlsrv') ? '<span class="ok">loaded</span>' : '<span class="err">missing</span>' ?> ·
       namespace <code><?= htmlspecialchars(getenv('NAMESPACE') ?: 'n/a') ?></code></p>
    <p>Probes: <code>/healthz.php</code> (liveness) · <code>/readyz.php</code> (readiness<?= strtolower((string)getenv('DB_REQUIRED'))==='true' ? ', DB-gated' : '' ?>)</p>
  </div>
</main>
<footer>Built from Git with the Docker strategy on OpenShift · see the guide in <code>docs/</code></footer>
</body>
</html>
