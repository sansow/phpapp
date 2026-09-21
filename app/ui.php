<?php
/** Shared layout: header with nav + footer. Keeps the demo pages consistent. */
function page_top(string $title, string $active): void {
    $pages = ['index.php' => 'Dashboard', 'guide.php' => 'Guide', 'visits.php' => 'Visits', 'about.php' => 'About'];
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
  /* rendered guide markdown */
  .guide-doc h1 { font-size: 1.45rem; margin-top: 0; }
  .guide-doc h2 { font-size: 1.15rem; text-transform: none; letter-spacing: 0; opacity: 1;
                  margin-top: 1.6rem; border-bottom: 1px solid rgba(128,128,128,.25); padding-bottom: .3rem; }
  .guide-doc pre { background: #151515; color: #e8e8e8; padding: 1rem; border-radius: 8px;
                   overflow-x: auto; font-size: .88rem; line-height: 1.45; }
  .guide-doc pre code { background: none; padding: 0; color: inherit; }
  .guide-doc blockquote { border-left: 4px solid #ee0000; margin: 1rem 0; padding: .3rem 1rem;
                          background: rgba(238,0,0,.06); border-radius: 0 6px 6px 0; }
  .guide-doc table { margin: 1rem 0; }
  .guide-doc a { color: #ee0000; }
  .copy-btn { position: absolute; top: .5rem; right: .5rem; background: rgba(255,255,255,.12);
              color: #fff; border: 1px solid rgba(255,255,255,.25); border-radius: 6px;
              padding: .25rem .7rem; font-size: .75rem; cursor: pointer; }
  .copy-btn:hover { background: rgba(255,255,255,.25); }
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
