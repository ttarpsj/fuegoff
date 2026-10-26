<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$output = __DIR__ . '/../dist';
if (!is_dir($output) && !mkdir($output, 0777, true) && !is_dir($output)) {
    throw new RuntimeException('Unable to create static export directory.');
}
file_put_contents($output . '/index.html', view('fuegoff')->render());
file_put_contents($output . '/.nojekyll', '');

$build = __DIR__ . '/../public/build';
if (!is_dir($build)) {
    throw new RuntimeException('Vite build output was not found. Run npm run build first.');
}
$target = $output . '/build';
if (is_dir($target)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $file) { $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname()); }
    rmdir($target);
}
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($build, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
foreach ($iterator as $file) {
    $destination = $target . DIRECTORY_SEPARATOR . substr($file->getPathname(), strlen($build) + 1);
    if ($file->isDir()) { is_dir($destination) || mkdir($destination, 0777, true); }
    else { is_dir(dirname($destination)) || mkdir(dirname($destination), 0777, true); copy($file->getPathname(), $destination); }
}
echo "Static site exported to dist/\n";
