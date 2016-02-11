<?php

include getenv('HTTP_BOOTSTRAP_WP');
if(ENVIRONMENT === 'prod') exit;

require_once __DIR__.'/StateToTermsImporter.php';

$tax_name = 'location'; // taxonomy to import terms into
$states_importer = new StateToTermsImporter($tax_name);
$states_importer->createTerms();