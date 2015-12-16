<?php
/**
 * create_xdebug_enabled_function.php
 *
 * @category    ClassyLlama
 * @package
 * @author      Erik Hansen <erik@classyllama.com>
 * @copyright   Copyright (c) 2015 Erik Hansen & Classy Llama Studios, LLC
 */

if (!function_exists('xdebug_is_enabled')) {
    function xdebug_is_enabled() {
        return true;
    }
}
