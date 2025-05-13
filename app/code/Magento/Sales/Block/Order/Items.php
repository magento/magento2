<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Block\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Items\AbstractItems;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Item\Collection;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;
use Magento\Theme\Block\Html\Pager;

/**
 * Sales order view items block.
 *
 * @api
 * @since 100.0.2
 */
class Items extends AbstractItems
{
    /**
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var int
     */
    private $itemsPerPage;

    /**
     * @var CollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var Collection|null
     */
    private $itemCollection;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     * @param CollectionFactory|null $itemCollectionFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = [],
        ?CollectionFactory $itemCollectionFactory = null
    ) {
        $this->_coreRegistry = $registry;
        $this->itemCollectionFactory = $itemCollectionFactory ?: ObjectManager::getInstance()
            ->get(CollectionFactory::class);
        parent::__construct($context, $data);
    }

    /**
     * Init pager block and item collection with page size and current page number
     *
     * @return $this
     * @since 100.1.7
     */
    protected function _prepareLayout()
    {
        $this->itemsPerPage = $this->_scopeConfig->getValue('sales/orders/items_per_page');
        $this->itemCollection = $this->createItemsCollection();

        /** @var Pager $pagerBlock */
        $pagerBlock = $this->getChildBlock('sales_order_item_pager');
        if ($pagerBlock) {
            $this->preparePager($pagerBlock);
        }

        return parent::_prepareLayout();
    }

    /**
     * Determine if the pager should be displayed for order items list.
     *
     * To be called from templates(after _prepareLayout()).
     *
     * @return bool
     * @since 100.1.7
     */
    public function isPagerDisplayed()
    {
        $pagerBlock = $this->getChildBlock('sales_order_item_pager');
        return $pagerBlock && ($this->itemCollection->getSize() > $this->itemsPerPage);
    }

    /**
     * Get visible items for current page.
     *
     * To be called from templates(after _prepareLayout()).
     *
     * @return \Magento\Framework\DataObject[]
     * @since 100.1.7
     */
    public function getItems()
    {
        return $this->itemCollection->getItems();
    }

    /**
     * Get pager HTML according to our requirements.
     *
     * To be called from templates(after _prepareLayout()).
     *
     * @return string HTML output
     * @since 100.1.7
     */
    public function getPagerHtml()
    {
        /** @var Pager $pagerBlock */
        $pagerBlock = $this->getChildBlock('sales_order_item_pager');
        return $pagerBlock ? $pagerBlock->toHtml() : '';
    }

    /**
     * Retrieve current order model instance
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * Prepare pager block
     *
     * @param AbstractBlock $pagerBlock
     */
    private function preparePager(AbstractBlock $pagerBlock): void
    {
        $collectionToPager = $this->itemCollection;
        $collectionToPager->addFieldToFilter('parent_item_id', ['null' => true]);
        $pagerBlock->setLimit($this->itemsPerPage);
        $pagerBlock->setAvailableLimit([$this->itemsPerPage]);
        $pagerBlock->setCollection($collectionToPager);
        $pagerBlock->setShowAmounts($this->isPagerDisplayed());
    }

    /**
     * Create items collection
     *
     * @return Collection
     */
    private function createItemsCollection(): Collection
    {
        $collection = $this->itemCollectionFactory->create();
        $collection->setOrderFilter($this->getOrder());

        return $collection;
    }
}
