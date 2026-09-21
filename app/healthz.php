<?php
// Liveness: is the PHP process able to execute? Never checks dependencies —
// a dependency outage must not restart app pods (see Module 1.6).
header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'pod' => gethostname()]);
