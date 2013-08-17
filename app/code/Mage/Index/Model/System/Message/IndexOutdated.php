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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Index_Model_System_Message_IndexOutdated implements Mage_AdminNotification_Model_System_MessageInterface
{
    /**
     * @var Mage_Core_Model_Factory_Helper
     */
    protected $_helperFactory;

    /**
     * @var Mage_Index_Model_Indexer
     */
    protected $_indexer;

    /**
     * @var Mage_Core_Model_UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var Magento_AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var array|null
     */
    protected $_indexes = null;

    /**
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Index_Model_Indexer $indexer
     * @param Mage_Core_Model_UrlInterface $urlBuilder
     * @param Magento_AuthorizationInterface $authorization
     */
    public function __construct(
        Mage_Core_Model_Factory_Helper $helperFactory,
        Mage_Index_Model_Indexer $indexer,
        Mage_Core_Model_UrlInterface $urlBuilder,
        Magento_AuthorizationInterface $authorization
    ) {
        $this->_helperFactory = $helperFactory;
        $this->_indexer = $indexer;
        $this->_urlBuilder = $urlBuilder;
        $this->_authorization = $authorization;
    }

    /**
     * @return array
     */
    protected function _getProcessesForReindex()
    {
        if ($this->_indexes === null) {
            $this->_indexes = array();
            $processes = $this->_indexer->getProcessesCollection()->addEventsStats();
            /** @var $process Mage_Index_Model_Process */
            foreach ($processes as $process) {
                if (($process->getStatus() == Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX
                    || $process->getEvents() > 0) && $process->getIndexer()->isVisible()
                ) {
                    $this->_indexes[] = $process->getIndexer()->getName();
                }
            }
        }
        return $this->_indexes;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        $data = $this->_getProcessesForReindex() ?: array();
        return md5('OUTDATED_INDEXES' . implode(':', $data));
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        return $this->_authorization->isAllowed('Mage_Index::index') && $this->_getProcessesForReindex();
    }

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText()
    {
        $data = $this->_getProcessesForReindex() ?: array();
        $indexList = implode(', ', $data);
        $url = $this->_urlBuilder->getUrl('adminhtml/process/list');
        $helper = $this->_helperFactory->get('Mage_Index_Helper_Data');
        $text = $helper->__('One or more of the Indexes are not up to date: %s', $indexList) . '. ';
        $text .= $helper->__('Please go to <a href="%s">Index Management</a> and rebuild required indexes.', $url);
        return $text;
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
    }
}
