<?php
require __DIR__ . '/db.php';
require __DIR__ . '/ui.php';

$pod = gethostname();
[$pdo, $dbStatus] = db_connect();
$visits  = null;
$dbError = null;

if ($pdo !== null) {
    try { $visits = db_record_visit($pdo); }
    catch (Throwable $e) { $dbError = $e->getMessage(); }
}
$connected = $pdo !== null && $dbError === null;

page_top('Dashboard', 'index.php');
?>
<div class="card">
  <h2>Serving pod</h2>
  <div class="big"><?= htmlspecialchars($pod) ?></div>
  <p>Scale the deployment and refresh — this name changes as the Route load-balances across replicas.</p>
</div>

<div class="card">
  <h2>SQL Server backend</h2>
  <?php if ($connected): ?>
    <div class="big ok">connected</div>
    <p>Total visits recorded in <code>dbo.visits</code>: <strong><?= (int) $visits ?></strong> — see the <a href="/visits.php">Visits</a> page for the log.</p>
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
  <h2>Probes</h2>
  <p><code>/healthz.php</code> (liveness) · <code>/readyz.php</code> (readiness<?= strtolower((string) getenv('DB_REQUIRED')) === 'true' ? ', gated on the DB' : '' ?>) — open them directly to see exactly what the kubelet polls.</p>
</div>
<?php page_bottom(); ?>
