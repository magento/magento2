<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Block\Dashboard;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Adminhtml dashboard tab abstract
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
abstract class AbstractDashboard extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Magento\Backend\Helper\Dashboard\AbstractDashboard
     * @since 2.0.0
     */
    protected $_dataHelper = null;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Order\CollectionFactory
     * @since 2.0.0
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return array|AbstractCollection|\Magento\Eav\Model\Entity\Collection\Abstract
     * @since 2.0.0
     */
    public function getCollection()
    {
        return $this->getDataHelper()->getCollection();
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function getCount()
    {
        return $this->getDataHelper()->getCount();
    }

    /**
     * Get data helper
     *
     * @return \Magento\Backend\Helper\Dashboard\AbstractDashboard
     * @since 2.0.0
     */
    public function getDataHelper()
    {
        return $this->_dataHelper;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareData()
    {
        return $this;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->_prepareData();
        return parent::_prepareLayout();
    }
}
