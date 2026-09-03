<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Api\WatchCatalogController;

$controller = new WatchCatalogController();

// Simulate uploading with 'rolex' (lowercase, which previously caused the unique slug crash!)
$req = Request::create('/api/watches', 'POST', [
    'brand_name'      => 'rolex',
    'model'           => 'Submariner Date Test',
    'reference'       => '126610-TEST',
    'condition'       => 'Unworn / New',
    'availability'    => 'AVAILABLE',
    'price'           => 225000000,
    'production_year' => 2024,
    'case_size'       => '41 mm',
    'case_material'   => 'Oystersteel',
    'movement'        => 'Calibre 3235',
    'box_papers'      => 'Full Set'
]);

$response = $controller->storeProduct($req);
echo "HTTP Status: " . $response->getStatusCode() . "\n";
echo "Response: " . $response->getContent() . "\n";
