<?php

if (!defined('_ATTACH_VENDOR_PATH_')) {
    $end   = strpos(__DIR__, DS . 'Config');
    $path  = substr(__DIR__, 0, $end) . DS . 'Vendor/';

    define('_ATTACH_VENDOR_PATH_', $path);
}
