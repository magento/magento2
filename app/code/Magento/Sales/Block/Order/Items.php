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
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection|null
     */
    private $itemCollection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @param int $itemsPerPage
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory|null $itemCollectionFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = [],
        $itemsPerPage = 20,
        $itemCollectionFactory = null
    ) {
        $this->_coreRegistry = $registry;
        $this->itemsPerPage = $itemsPerPage;
        $this->itemCollectionFactory = $itemCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get order item collection factory.
     * Backward compatible way to add new dependency.
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    private function getItemCollectionFactory()
    {
        if ($this->itemCollectionFactory === null) {
            $this->itemCollectionFactory = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory::class);
        }
        return $this->itemCollectionFactory;
    }

    /**
     * Create collection and init it with page and item filters.
     * Only visible order items are filtered
     * Page filters are also applied
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Item\Collection|null
     */
    private function getCollection()
    {
        if ($this->itemCollection != null) {
            return $this->itemCollection;
        }
        $this->itemCollection = $this->getItemCollectionFactory()->create();
        $this->itemCollection->setOrderFilter($this->getOrder());
        $this->itemCollection->filterByParent(null);
        /** @var \Magento\Theme\Block\Html\Pager $pagerBlock */
        $pagerBlock = $this->getChildBlock('sales_order_item_pager');
        $pagerBlock->setLimit($this->itemsPerPage);
        $pagerBlock->setCollection($this->itemCollection);
        return $this->itemCollection;
    }

    /**
     * Determine if the pager should be displayed for order items list
     *
     * @return bool
     */
    private function isPagerDisplayed()
    {
        return $this->getCollection()->getSize() > $this->itemsPerPage;
    }

    /**
     * Get visible items for current page.
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getItems()
    {
        return $this->getCollection()->getItems();
    }

    /**
     * Get pager HTML according to our requirements
     *
     * @return string HTML output
     */
    public function getPagerHtml()
    {
        // make sure collection is initialized with page data
        $this->getCollection();
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
