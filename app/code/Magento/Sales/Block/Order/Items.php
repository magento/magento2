<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sales order view items block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Sales\Block\Order;

class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Order items per page.
     *
     * @var int
     */
    private $itemsPerPage;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $globalConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection|null
     */
    private $itemCollection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @param \Magento\Framework\App\Config\ScopeConfigInterface|null $globalConfig
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory|null $itemCollectionFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = [],
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig = null,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $itemCollectionFactory = null
    ) {
        $this->_coreRegistry = $registry;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->itemCollectionFactory = $itemCollectionFactory ?: $objectManager
            ->get(\Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory::class);
        $this->globalConfig = $globalConfig ?: $objectManager
            ->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        parent::__construct($context, $data);
    }

    /**
     * Init pager block and item collection with page size and current page number
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->itemsPerPage = $this->globalConfig->getValue('sales/orders/items_per_page');

        $this->itemCollection = $this->itemCollectionFactory->create();
        $this->itemCollection->setOrderFilter($this->getOrder());
        $this->itemCollection->filterByParent(null);

        /** @var \Magento\Theme\Block\Html\Pager $pagerBlock */
        $pagerBlock = $this->getChildBlock('sales_order_item_pager');
        $pagerBlock->setLimit($this->itemsPerPage);
        //here pager updates collection parameters
        $pagerBlock->setCollection($this->itemCollection);

        return parent::_prepareLayout();
    }

    /**
     * Determine if the pager should be displayed for order items list
     *
     * @return bool
     */
    private function isPagerDisplayed()
    {
        return $this->itemCollection->getSize() > $this->itemsPerPage;
    }

    /**
     * Get visible items for current page.
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getItems()
    {
        return $this->itemCollection->getItems();
    }

    /**
     * Get pager HTML according to our requirements
     *
     * @return string HTML output
     */
    public function getPagerHtml()
    {
        /** @var \Magento\Theme\Block\Html\Pager $pagerBlock */
        $pagerBlock = $this->getChildBlock('sales_order_item_pager');
        $pagerBlock->setAvailableLimit([$this->itemsPerPage]);
        $pagerBlock->setShowAmounts($this->isPagerDisplayed());

        return $pagerBlock->toHtml();
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }
}
