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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Log_Model_Shell_Command_Clean implements Mage_Log_Model_Shell_CommandInterface
{
    /**
     * @var Mage_Core_Model_StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Mage_Log_Model_LogFactory
     */
    protected $_logFactory;

    /**
     * Clean after days count
     *
     * @var int
     */
    protected $_days;

    public function __construct(
        Mage_Core_Model_StoreManagerInterface $storeManager,
        Mage_Log_Model_LogFactory $logFactory,
        $days
    ) {
        $this->_storeManager = $storeManager;
        $this->_logFactory = $logFactory;
        $this->_days = $days;
    }

    /**
     * Execute command
     *
     * @return string
     */
    public function execute()
    {
        if ($this->_days > 0) {
            $this->_storeManager->getStore()->setConfig(Mage_Log_Model_Log::XML_LOG_CLEAN_DAYS, $this->_days);
        }
        /** @var $model Mage_Log_Model_Log */
        $model = $this->_logFactory->create();
        $model->clean();
        return "Log cleaned\n";
    }
}