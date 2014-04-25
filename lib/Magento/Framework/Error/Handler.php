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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Error;

/**
 * Default Error Handler
 */
class Handler implements HandlerInterface
{
    /**
     * Error messages
     *
     * @var array
     */
    protected $errorPhrases = array(
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parse Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Strict Notice',
        E_RECOVERABLE_ERROR => 'Recoverable Error',
        E_DEPRECATED => 'Deprecated Functionality',
        E_USER_DEPRECATED => 'User Deprecated Functionality'
    );

    /**
     * Process exception
     *
     * @param \Exception $exception
     * @param string[] $params
     * @return void
     */
    public function processException(\Exception $exception, array $params = array())
    {
        echo '<pre>';
        echo $exception->getMessage() . "\n\n";
        echo $exception->getTraceAsString();
        echo '</pre>';
    }

    /**
     * Show error as exception
     *
     * @param string $errorMessage
     * @return void
     * @throws \Exception
     */
    protected function _processError($errorMessage)
    {
        throw new \Exception($errorMessage);
    }

    /**
     * Custom error handler
     *
     * @param int $errorNo
     * @param string $errorStr
     * @param string $errorFile
     * @param int $errorLine
     * @return bool
     */
    public function handler($errorNo, $errorStr, $errorFile, $errorLine)
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
            if ($errorNo == E_STRICT || $errorNo == E_DEPRECATED) {
                return true;
            }
            // ignore attempts to read system files when open_basedir is set
            if ($errorNo == E_WARNING && stripos($errorStr, 'open_basedir') !== false) {
                return true;
            }
        }
        $errorMessage = isset(
            $this->errorPhrases[$errorNo]
        ) ? $this->errorPhrases[$errorNo] : "Unknown error ({$errorNo})";
        $errorMessage .= ": {$errorStr} in {$errorFile} on line {$errorLine}";
        $this->_processError($errorMessage);
        return true;
    }
}
