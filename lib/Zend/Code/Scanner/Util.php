<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Code
 */

namespace Zend\Code\Scanner;

use stdClass;
use Zend\Code\Exception;

/**
 * Shared utility methods used by scanners
 *
 * @package    Zend_Code
 * @subpackage Scanner
 */
class Util
{
    public static function resolveImports(&$value, $key = null, stdClass $data)
    {
        if (!property_exists($data, 'uses') || !property_exists($data, 'namespace')) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '%s expects a data object containing "uses" and "namespace" properties; on or both missing',
                    __METHOD__
                ));
        }

        if ($data->namespace && !$data->uses && strlen($value) > 0 && $value{0} != '\\') {
            $value = $data->namespace . '\\' . $value;
            return;
        }

        if (!$data->uses || strlen($value) <= 0 || $value{0} == '\\') {
            $value = ltrim($value, '\\');
            return;
        }

        if ($data->namespace || $data->uses) {
            $firstPart = $value;
            if (($firstPartEnd = strpos($firstPart, '\\')) !== false) {
                $firstPart = substr($firstPart, 0, $firstPartEnd);
            } else {
                $firstPartEnd = strlen($firstPart);
            }
            if (array_key_exists($firstPart, $data->uses)) {
                $value = substr_replace($value, $data->uses[$firstPart], 0, $firstPartEnd);
                return;
            }
            if ($data->namespace) {
                $value = $data->namespace . '\\' . $value;
                return;
            }
        }
    }
}
