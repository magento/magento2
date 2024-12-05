<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Customer\Block\Adminhtml\Edit\Tab\View\Grid\Renderer\Item;
use Magento\Customer\Block\Adminhtml\Grid\Renderer\Multiaction;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Adminhtml customer orders grid block
 *
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Cart extends Extended
{
    /**
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var CollectionFactory
     */
    protected $_dataCollectionFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Quote
     */
    protected $quote = null;

    /**
     * @var string
     */
    protected $_parentTemplate;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;
    /**
     * @var SystemStore
     */
    private $systemStore;
    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param CartRepositoryInterface $quoteRepository
     * @param CollectionFactory $dataCollectionFactory
     * @param Registry $coreRegistry
     * @param QuoteFactory $quoteFactory
     * @param array $data
     * @param SystemStore|null $systemStore
     * @param FormFactory|null $formFactory
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CartRepositoryInterface $quoteRepository,
        CollectionFactory $dataCollectionFactory,
        Registry $coreRegistry,
        QuoteFactory $quoteFactory,
        array $data = [],
        ?SystemStore $systemStore = null,
        ?FormFactory $formFactory = null
    ) {
        $this->_dataCollectionFactory = $dataCollectionFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->quoteRepository = $quoteRepository;
        $this->quoteFactory = $quoteFactory;
        $this->systemStore = $systemStore ?? ObjectManager::getInstance()->get(SystemStore::class);
        $this->formFactory = $formFactory ?? ObjectManager::getInstance()->get(FormFactory::class);
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
        $this->_parentTemplate = $this->getTemplate();
        $this->setTemplate('Magento_Customer::tab/cart.phtml');
    }

    /**
     * Prepare grid
     *
     * @return void
     */
    protected function _prepareGrid()
    {
        $this->setId('customer_cart_grid');
        parent::_prepareGrid();
        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->prepareWebsiteFilter();
        }
    }

    /**
     * Prepare collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $quote = $this->getQuote();

        if ($quote && $quote->getId()) {
            $collection = $quote->getItemsCollection(false);
            $collection->addFieldToFilter('parent_item_id', ['null' => true]);
        } else {
            $collection = $this->_dataCollectionFactory->create();
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareColumns()
    {
        $this->addColumn('product_id', ['header' => __('ID'), 'index' => 'product_id', 'width' => '100px']);

        $this->addColumn(
            'name',
            [
                'header' => __('Product'),
                'index' => 'name',
                'renderer' => Item::class
            ]
        );

        $this->addColumn('sku', ['header' => __('SKU'), 'index' => 'sku', 'width' => '100px']);

        $this->addColumn(
            'qty',
            ['header' => __('Quantity'), 'index' => 'qty', 'type' => 'number', 'width' => '60px']
        );

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

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'index' => 'quote_item_id',
                'renderer' => Multiaction::class,
                'filter' => false,
                'sortable' => false,
                'actions' => [
                    [
                        'caption' => __('Configure'),
                        'url' => 'javascript:void(0)',
                        'process' => 'configurable',
                        'control_object' => $this->getJsObjectName() . 'cartControl',
                    ],
                    [
                        'caption' => __('Delete'),
                        'url' => '#',
                        'onclick' => 'return ' . $this->getJsObjectName() . 'cartControl.removeItem($item_id);'
                    ],
                ]
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Gets customer assigned to this block
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function getGridUrl()
    {
        return $this->getUrl('customer/*/cart', ['_current' => true, 'website_id' => $this->getWebsiteId()]);
    }

    /**
     * Gets grid parent html
     *
     * @return string
     */
    public function getGridParentHtml()
    {
        $templateName = $this->resolver->getTemplateFileName($this->_parentTemplate, ['_relative' => true]);
        return $this->fetchView($templateName);
    }

    /**
     * @inheritdoc
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'catalog/product/edit',
            [
                'id' => $row->getProductId(),
                'customerId' => $this->getCustomerId()
            ]
        );
    }

    /**
     * Get the quote of the cart
     *
     * @return Quote
     * @throws LocalizedException
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $customerId = $this->getCustomerId();
            $websiteId = $this->getWebsiteId() ?:
                $this->_storeManager->getDefaultStoreView()->getWebsiteId();
            $storeIds = $this->getAssociatedStoreIds((int) $websiteId);
            try {
                $this->quote = $this->quoteRepository->getForCustomer($customerId, $storeIds);
            } catch (NoSuchEntityException $e) {
                $this->quote = $this->quoteFactory->create()->setSharedStoreIds($storeIds);
            }
        }
        return $this->quote;
    }

    /**
     * Add website filter block to the layout
     *
     * @return void
     */
    private function prepareWebsiteFilter(): void
    {
        $form = $this->formFactory->create();
        $form->addField(
            'website_filter',
            'select',
            [
                'name' => 'website_id',
                'values' => $this->systemStore->getWebsiteOptionHash(),
                'value' => $this->getWebsiteId() ?? $this->_storeManager->getWebsite()->getId(),
                'no_span' => true,
                'onchange' => "{$this->getJsObjectName()}.loadByElement(this);",
            ]
        );
        /**
         * @var Form $formWidget
         */
        $formWidget = $this->getLayout()->createBlock(Form::class);
        $formWidget->setForm($form);
        $formWidget->setTemplate('Magento_Customer::tab/cart_website_filter_form.phtml');
        $this->setChild(
            'website_filter_block',
            $formWidget
        );
    }

    /**
     * @inheritDoc
     * @since 103.0.0
     */
    public function getMainButtonsHtml()
    {
        return $this->getWebsiteFilterHtml() . parent::getMainButtonsHtml();
    }

    /**
     * Generate website filter
     *
     * @return string
     */
    private function getWebsiteFilterHtml(): string
    {
        return $this->getChildHtml('website_filter_block');
    }

    /**
     * Get website associated store IDs
     *
     * @param int $websiteId
     * @return array
     * @throws LocalizedException
     */
    private function getAssociatedStoreIds(int $websiteId): array
    {
        $storeIds = $this->_storeManager->getWebsite($websiteId)->getStoreIds();
        if (empty($this->getWebsiteId()) && !empty($this->_storeManager->getWebsite()->getStoreIds())) {
            $storeIds = $this->_storeManager->getWebsite()->getStoreIds();
        }
        return $storeIds;
    }
}
