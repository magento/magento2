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

/**
 * Default Error Handler
 */
namespace Magento\App\Error;

class Handler extends \Magento\Error\Handler
{
    /**
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\App\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * @param \Magento\Logger $logger
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\App\State $appState
     */
    public function __construct(
        \Magento\Logger $logger,
        \Magento\App\Filesystem $filesystem,
        \Magento\App\State $appState
    ) {
        $this->_logger = $logger;
        $this->_filesystem = $filesystem;
        $this->_appState = $appState;
    }

    /**
     * Process exception
     *
     * @param \Exception $exception
     * @param array $params
     * @return void
     */
    public function processException(\Exception $exception, array $params = array())
    {
        if ($this->_appState->getMode() == \Magento\App\State::MODE_DEVELOPER) {
            parent::processException($exception, $params);
        } else {
            $reportData = array($exception->getMessage(), $exception->getTraceAsString()) + $params;
            // retrieve server data
            if (isset($_SERVER)) {
                if (isset($_SERVER['REQUEST_URI'])) {
                    $reportData['url'] = $_SERVER['REQUEST_URI'];
                }
                if (isset($_SERVER['SCRIPT_NAME'])) {
                    $reportData['script_name'] = $_SERVER['SCRIPT_NAME'];
                }
            }
            require_once $this->_filesystem->getPath(\Magento\App\Filesystem::PUB_DIR) . '/errors/report.php';
        }
    }

    /**
     * Show error as exception or log it
     *
     * @param string $errorMessage
     * @throws \Exception
     * @return void
     */
    protected function _processError($errorMessage)
    {
        if ($this->_appState->getMode() == \Magento\App\State::MODE_DEVELOPER) {
            parent::_processError($errorMessage);
        } else {
            $exception = new \Exception($errorMessage);
            $errorMessage .= $exception->getTraceAsString();
            $this->_logger->log($errorMessage, \Zend_Log::ERR);
        }
    }
}
