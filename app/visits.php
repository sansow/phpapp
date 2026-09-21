<?php
require __DIR__ . '/db.php';
require __DIR__ . '/ui.php';

[$pdo, $status] = db_connect();
$rows = [];
$error = null;
if ($pdo !== null) {
    try {
        $rows = $pdo->query(
            'SELECT TOP 20 id, pod, ts FROM dbo.visits ORDER BY id DESC'
        )->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

page_top('Visits', 'visits.php');
?>
<div class="card">
  <h2>Recent visits (from SQL Server)</h2>
  <?php if ($pdo === null): ?>
    <div class="big warn"><?= htmlspecialchars($status) ?></div>
    <p>This page reads from the <code>dbo.visits</code> table — deploy the backend in <strong>Module 1.8</strong> to populate it.</p>
  <?php elseif ($error !== null): ?>
    <div class="big err">query failed</div>
    <p><code><?= htmlspecialchars($error) ?></code></p>
    <p>If the table doesn't exist yet, load the Dashboard once — it creates and seeds it.</p>
  <?php elseif (!$rows): ?>
    <p>Connected, but no visits recorded yet — load the Dashboard to add the first one.</p>
  <?php else: ?>
    <table>
      <tr><th>#</th><th>Served by pod</th><th>Timestamp (UTC)</th></tr>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= (int) $r['id'] ?></td>
        <td><code><?= htmlspecialchars($r['pod']) ?></code></td>
        <td><?= htmlspecialchars(is_object($r['ts']) ? $r['ts']->format('Y-m-d H:i:s') : (string) $r['ts']) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <p>Scale the deployment and refresh the Dashboard from another browser — different pod names appear here. One shared database, many stateless replicas.</p>
  <?php endif; ?>
</div>
<?php page_bottom(); ?>
