<?php

use Cake\Core\Configure;

try {
    Configure::load('RestApi.api', 'default', false);
    Configure::load('api', 'default', true);
} catch (\Exception $e) {
    // nothing
}

// Optionally load additional queue config defaults from local app config
if (file_exists(ROOT . DS . 'config' . DS . 'app_rest_api.php')) {
    Configure::load('app_rest_api');
}