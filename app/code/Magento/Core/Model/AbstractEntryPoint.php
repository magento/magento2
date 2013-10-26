<?php
/**
 * Abstract application entry point
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model;

abstract class AbstractEntryPoint
{
    /**
     * Application configuration
     *
     * @var \Magento\Core\Model\Config\Primary
     */
    protected $_config;

    /**
     * Application object manager
     *
     * @var \Magento\Core\Model\ObjectManager
     */
    protected $_objectManager;

    /**
     * @param \Magento\Core\Model\Config\Primary $config
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(
        \Magento\Core\Model\Config\Primary $config,
        \Magento\ObjectManager $objectManager = null
    ) {
        $this->_config = $config;
        $this->_objectManager = $objectManager;
    }

    /**
     * Process request by the application
     */
    public function processRequest()
    {
        $this->_init();
        $this->_processRequest();
    }

    /**
     * Process exception
     *
     * @param \Exception $exception
     */
    public function processException(\Exception $exception)
    {
        $this->_init();
        $appMode = $this->_objectManager->get('Magento\App\State')->getMode();
        if ($appMode == \Magento\App\State::MODE_DEVELOPER) {
            print '<pre>';
            print $exception->getMessage() . "\n\n";
            print $exception->getTraceAsString();
            print '</pre>';
        } else {
            $reportData = array($exception->getMessage(), $exception->getTraceAsString());

            // retrieve server data
            if (isset($_SERVER)) {
                if (isset($_SERVER['REQUEST_URI'])) {
                    $reportData['url'] = $_SERVER['REQUEST_URI'];
                }
                if (isset($_SERVER['SCRIPT_NAME'])) {
                    $reportData['script_name'] = $_SERVER['SCRIPT_NAME'];
                }
            }

            // attempt to specify store as a skin
            try {
                $storeManager = $this->_objectManager->get('Magento\Core\Model\StoreManager');
                $reportData['skin'] = $storeManager->getStore()->getCode();
            } catch (\Exception $exception) {
            }

            $modelDir = $this->_objectManager->get('Magento\App\Dir');
            require_once($modelDir->getDir(\Magento\App\Dir::PUB) . DS . 'errors' . DS . 'report.php');
        }
    }

    /**
     * Initializes the entry point, so a Magento application is ready to be used
     */
    protected function _init()
    {
        if (!$this->_objectManager) {
            $this->_objectManager = new \Magento\Core\Model\ObjectManager($this->_config);
        }
    }

    /**
     * Template method to process request according to the actual entry point rules
     */
    protected abstract function _processRequest();
}

