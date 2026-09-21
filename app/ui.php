<?php
/** Shared layout: header with nav + footer. Keeps the demo pages consistent. */
function page_top(string $title, string $active): void {
    $pages = ['index.php' => 'Dashboard', 'visits.php' => 'Visits', 'about.php' => 'About'];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($title) ?> — OpenShift PHP Demo</title>
<style>
  :root { color-scheme: light dark; }
  body { font-family: "Red Hat Text", system-ui, sans-serif; margin: 0;
         background: #f2f2f2; color: #151515; }
  @media (prefers-color-scheme: dark) { body { background:#1b1b1d; color:#eee; } .card{background:#26262a!important;} }
  header { background: #ee0000; color: #fff; padding: 1rem 2rem 0; }
  header h1 { margin: 0 0 .8rem; font-size: 1.25rem; font-weight: 500; }
  nav { display: flex; gap: .25rem; }
  nav a { color: #fff; text-decoration: none; padding: .5rem 1rem;
          border-radius: 6px 6px 0 0; font-size: .95rem; opacity: .85; }
  nav a:hover { background: rgba(255,255,255,.15); opacity: 1; }
  nav a.active { background: #f2f2f2; color: #151515; opacity: 1; font-weight: 600; }
  @media (prefers-color-scheme: dark) { nav a.active { background:#1b1b1d; color:#eee; } }
  main { max-width: 720px; margin: 2rem auto; padding: 0 1rem; }
  .card { background: #fff; border-radius: 8px; padding: 1.2rem 1.5rem;
          margin-bottom: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.15); }
  .card h2 { margin: 0 0 .6rem; font-size: 1rem; text-transform: uppercase;
             letter-spacing: .05em; opacity: .7; }
  .big { font-size: 1.6rem; font-weight: 600; }
  .ok { color: #3e8635; } .warn { color: #f0ab00; } .err { color: #c9190b; }
  code { background: rgba(128,128,128,.15); padding: .1em .35em; border-radius: 4px; }
  table { width: 100%; border-collapse: collapse; font-size: .95rem; }
  th, td { text-align: left; padding: .45rem .6rem; border-bottom: 1px solid rgba(128,128,128,.25); }
  th { text-transform: uppercase; font-size: .75rem; letter-spacing: .05em; opacity: .7; }
  footer { text-align: center; font-size: .8rem; opacity: .6; margin: 2rem 0; }
</style>
</head>
<body>
<header>
  <h1>OpenShift 4.22 Getting Started — PHP Demo</h1>
  <nav>
    <?php foreach ($pages as $file => $label): ?>
      <a href="/<?= $file ?>"<?= $file === $active ? ' class="active"' : '' ?>><?= $label ?></a>
    <?php endforeach; ?>
  </nav>
</header>
<main>
<?php
}

function page_bottom(): void {
    ?>
</main>
<footer>Served by pod <code><?= htmlspecialchars(gethostname()) ?></code> · built from Git on OpenShift · guide in <code>docs/</code></footer>
</body>
</html>
<?php
}
