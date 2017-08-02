<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Newsletter subscribers grid website filter
 */
namespace Magento\Newsletter\Block\Adminhtml\Subscriber\Grid\Filter;

use Magento\Store\Model\ResourceModel\Website\Collection;

/**
 * Class \Magento\Newsletter\Block\Adminhtml\Subscriber\Grid\Filter\Website
 *
 * @since 2.0.0
 */
class Website extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * Website collection
     *
     * @var Collection
     * @since 2.0.0
     */
    protected $_websiteCollection = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\CollectionFactory
     * @since 2.0.0
     */
    protected $_websitesFactory;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websitesFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Store\Model\ResourceModel\Website\CollectionFactory $websitesFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_storeManager = $storeManager;
        $this->_websitesFactory = $websitesFactory;
        parent::__construct($context, $resourceHelper, $data);
    }

    /**
     * Get options for grid filter
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getOptions()
    {
        $result = $this->getCollection()->toOptionArray();
        array_unshift($result, ['label' => null, 'value' => null]);
        return $result;
    }

    /**
     * @return Collection|null
     * @since 2.0.0
     */
    public function getCollection()
    {
        if ($this->_websiteCollection === null) {
            $this->_websiteCollection = $this->_websitesFactory->create()->load();
        }

        $this->_coreRegistry->register('website_collection', $this->_websiteCollection);

        return $this->_websiteCollection;
    }

    /**
     * Get options for grid filter
     *
     * @return null|array
     * @since 2.0.0
     */
    public function getCondition()
    {
        $id = $this->getValue();
        if (!$id) {
            return null;
        }

        $website = $this->_storeManager->getWebsite($id);
        return ['in' => $website->getStoresIds(true)];
    }
}
