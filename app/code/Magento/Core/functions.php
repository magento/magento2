<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Object destructor
 *
 * @param mixed $object
 */
function destruct($object)
{
    if (is_array($object)) {
        foreach ($object as $obj) {
            destruct($obj);
        }
    }
    unset($object);
}

/**
 * Tiny function to enhance functionality of ucwords
 *
 * Will capitalize first letters and convert separators if needed
 *
 * @param string $str
 * @param string $destSep
 * @param string $srcSep
 * @return string
 */
function uc_words($str, $destSep = '_', $srcSep = '_')
{
    return str_replace(' ', $destSep, ucwords(str_replace($srcSep, ' ', $str)));
}

/**
 * Simple sql format date
 *
 * @param bool $dayOnly
 * @return string
 */
function now($dayOnly = false)
{
    return date($dayOnly ? 'Y-m-d' : 'Y-m-d H:i:s');
}

/**
 * Check whether sql date is empty
 *
 * @param string $date
 * @return boolean
 */
function is_empty_date($date)
{
    return preg_replace('#[ 0:-]#', '', $date) === '';
}

/**
 * Custom error handler
 *
 * @param integer $errorNo
 * @param string $errorStr
 * @param string $errorFile
 * @param integer $errorLine
 * @return bool
 * @throws \Exception
 */
function mageCoreErrorHandler($errorNo, $errorStr, $errorFile, $errorLine)
{
    if (strpos($errorStr, 'DateTimeZone::__construct') !== false) {
        // there's no way to distinguish between caught system exceptions and warnings
        return false;
    }

    $errorNo = $errorNo & error_reporting();
    if ($errorNo == 0) {
        return false;
    }

    // PEAR specific message handling
    if (stripos($errorFile . $errorStr, 'pear') !== false) {
         // ignore strict and deprecated notices
        if (($errorNo == E_STRICT) || ($errorNo == E_DEPRECATED)) {
            return true;
        }
        // ignore attempts to read system files when open_basedir is set
        if ($errorNo == E_WARNING && stripos($errorStr, 'open_basedir') !== false) {
            return true;
        }
    }

    $errorMessage = '';

    switch ($errorNo) {
        case E_ERROR:
            $errorMessage .= "Error";
            break;
        case E_WARNING:
            $errorMessage .= "Warning";
            break;
        case E_PARSE:
            $errorMessage .= "Parse Error";
            break;
        case E_NOTICE:
            $errorMessage .= "Notice";
            break;
        case E_CORE_ERROR:
            $errorMessage .= "Core Error";
            break;
        case E_CORE_WARNING:
            $errorMessage .= "Core Warning";
            break;
        case E_COMPILE_ERROR:
            $errorMessage .= "Compile Error";
            break;
        case E_COMPILE_WARNING:
            $errorMessage .= "Compile Warning";
            break;
        case E_USER_ERROR:
            $errorMessage .= "User Error";
            break;
        case E_USER_WARNING:
            $errorMessage .= "User Warning";
            break;
        case E_USER_NOTICE:
            $errorMessage .= "User Notice";
            break;
        case E_STRICT:
            $errorMessage .= "Strict Notice";
            break;
        case E_RECOVERABLE_ERROR:
            $errorMessage .= "Recoverable Error";
            break;
        case E_DEPRECATED:
            $errorMessage .= "Deprecated functionality";
            break;
        default:
            $errorMessage .= "Unknown error ($errorNo)";
            break;
    }

    $errorMessage .= ": {$errorStr} in {$errorFile} on line {$errorLine}";
    $exception = new \Exception($errorMessage);
    $errorMessage .= $exception->getTraceAsString();
    $appState = \Magento\Core\Model\ObjectManager::getInstance()->get('Magento\App\State');
    if ($appState == \Magento\App\State::MODE_DEVELOPER) {
        throw $exception;
    } else {
        $dirs = new \Magento\App\Dir('.');
        $fileSystem = new \Magento\Io\File();
        $logger = new \Magento\Core\Model\Logger($dirs, $fileSystem);
        $logger->log($errorMessage, \Zend_Log::ERR);
    }
}

/**
 * Pretty debug backtrace
 *
 * @param bool $return
 * @param bool $html
 * @param bool $showFirst
 * @return string
 */
function mageDebugBacktrace($return = false, $html = true, $showFirst = false)
{
    $backTrace = debug_backtrace();
    $out = '';
    if ($html) {
        $out .= "<pre>";
    }

    foreach ($backTrace as $index => $trace) {
        if (!$showFirst && $index == 0) {
            continue;
        }
        // sometimes there is undefined index 'file'
        @$out .= "[$index] {$trace['file']}:{$trace['line']}\n";
    }

    if ($html) {
        $out .= "</pre>";
    }

    if ($return) {
        return $out;
    } else {
        echo $out;
    }
}

/**
 * Delete folder recursively
 *
 * @param string $path
 */
function mageDelTree($path)
{
    if (is_dir($path)) {
        $entries = scandir($path);
        foreach ($entries as $entry) {
            if ($entry != '.' && $entry != '..') {
                mageDelTree($path . DS . $entry);
            }
        }
        @rmdir($path);
    } else {
        @unlink($path);
    }
}

/**
 * Parse csv file
 *
 * @param string $string
 * @param string $delimiter
 * @param string $enclosure
 * @return array
 */
function mageParseCsv($string, $delimiter = ",", $enclosure = '"')
{
    $elements = explode($delimiter, $string);
    for ($i = 0; $i < count($elements); $i++) {
        $nQuotes = substr_count($elements[$i], $enclosure);
        if ($nQuotes %2 == 1) {
            for ($j = $i+1; $j < count($elements); $j++) {
                if (substr_count($elements[$j], $enclosure) > 0) {
                    // Put the quoted string's pieces back together again
                    array_splice($elements, $i, $j - $i + 1,
                        implode($delimiter, array_slice($elements, $i, $j - $i + 1)));
                    break;
                }
            }
        }
        if ($nQuotes > 0) {
            // Remove first and last quotes, then merge pairs of quotes
            $qStr =& $elements[$i];
            $qStr = substr_replace($qStr, '', strpos($qStr, $enclosure), 1);
            $qStr = substr_replace($qStr, '', strrpos($qStr, $enclosure), 1);
            $qStr = str_replace($enclosure.$enclosure, $enclosure, $qStr);
        }
    }
    return $elements;
}

/**
 * Check is directory writable or not
 *
 * @param string $dir
 * @return bool
 */
function is_dir_writeable($dir)
{
    if (is_dir($dir) && is_writable($dir)) {
        if (stripos(PHP_OS, 'win') === 0) {
            $dir    = ltrim($dir, DIRECTORY_SEPARATOR);
            $file   = $dir . DIRECTORY_SEPARATOR . uniqid(mt_rand()) . '.tmp';
            $exist  = file_exists($file);
            $fileResource = @fopen($file, 'a');
            if ($fileResource === false) {
                return false;
            }
            fclose($fileResource);
            if (!$exist) {
                unlink($file);
            }
        }
        return true;
    }
    return false;
}

/**
 * Create value-object \Magento\Phrase
 *
 * @return string
 */
function __()
{
    $argc = func_get_args();

    /**
     * Type casting to string is a workaround.
     * Many places in client code at the moment are unable to handle the \Magento\Phrase object properly.
     * The intended behavior is to use __toString(),
     * so that rendering of the phrase happens only at the last moment when needed
     */
    return (string)new \Magento\Phrase(array_shift($argc), $argc);
}
