<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Directory\Model\Currency;

/**
 * Adminhtml customer cart items grid block
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Cart extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $_dataCollectionFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote = null;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Data\CollectionFactory $dataCollectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Data\CollectionFactory $dataCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        array $data = []
    ) {
        $this->_dataCollectionFactory = $dataCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->quoteRepository = $quoteRepository;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_view_cart_grid');
        $this->setDefaultSort('added_at', 'desc');
        $this->setSortable(false);
        $this->setPagerVisibility(false);
        $this->setFilterVisibility(false);
        $this->setEmptyText(__('There are no items in customer\'s shopping cart.'));
    }

    /**
     * Prepare the cart collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $quote = $this->getQuote();

        if ($quote) {
            $collection = $quote->getItemsCollection(false);
        } else {
            $collection = $this->_dataCollectionFactory->create();
        }

        $collection->addFieldToFilter('parent_item_id', ['null' => true]);
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('product_id', ['header' => __('ID'), 'index' => 'product_id', 'width' => '100px']);

        $this->addColumn('name', ['header' => __('Product'), 'index' => 'name']);

        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku', 'width' => '100px']);

        $this->addColumn('qty', ['header' => __('Qty'), 'index' => 'qty', 'type' => 'number', 'width' => '60px']);

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'index' => 'price',
                'type' => 'currency',
                'currency_code' => $this->getQuote()->getQuoteCurrencyCode(),
                'rate' => $this->getQuote()->getBaseToQuoteRate(),
            ]
        );

        $this->addColumn(
            'total',
            [
                'header' => __('Total'),
                'index' => 'row_total',
                'type' => 'currency',
                'currency_code' => $this->getQuote()->getQuoteCurrencyCode(),
                'rate' => 1,
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('catalog/product/edit', ['id' => $row->getProductId()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeadersVisibility()
    {
        return $this->getCollection()->getSize() >= 0;
    }

    /**
     * Get quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (null == $this->quote) {
            $storeIds = $this->_storeManager->getWebsite($this->getWebsiteId())->getStoreIds();
            $this->quote = $this->quoteFactory->create()->setSharedStoreIds($storeIds);

            $currentCustomerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
            if (!empty($currentCustomerId)) {
                try {
                    $this->quote = $this->quoteRepository->getForCustomer($currentCustomerId, $storeIds);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                }
            }
        }
        return $this->quote;
    }
}
