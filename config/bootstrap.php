<?php

use Cake\Core\Configure;

try {
    Configure::load('RestApi.api', 'default', false);
    Configure::load('api', 'default', true);
} catch (\Exception $e) {
    // nothing
}