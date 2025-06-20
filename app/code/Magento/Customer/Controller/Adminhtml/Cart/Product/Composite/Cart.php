<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Controller\Adminhtml\Cart\Product\Composite;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\QuoteItemRetriever;
use Magento\Framework\AuthorizationInterface;

/**
 * Catalog composite product configuration controller
 */
abstract class Cart extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Customer::manage';

    /**
     * Customer we're working with
     *
     * @var int id of the customer
     */
    protected $_customerId;

    /**
     * Quote we're working with
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote = null;

    /**
     * Quote item we're working with
     *
     * @var Item
     */
    protected $_quoteItem = null;

    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var QuoteItemRetriever
     */
    private $quoteItemRetriever;

    /**
     * @var AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @param Action\Context $context
     * @param CartRepositoryInterface $quoteRepository
     * @param QuoteFactory $quoteFactory
     * @param QuoteItemRetriever $quoteItemRetriever
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        Action\Context $context,
        CartRepositoryInterface $quoteRepository,
        QuoteFactory $quoteFactory,
        QuoteItemRetriever $quoteItemRetriever,
        AuthorizationInterface $authorization
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteFactory = $quoteFactory;
        $this->quoteItemRetriever = $quoteItemRetriever;
        $this->_authorization = $authorization;
        parent::__construct($context);
    }

    /**
     * Loads customer, quote and quote item by request params
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initData()
    {
        $this->_customerId = (int)$this->getRequest()->getParam('customer_id');
        if (!$this->_customerId) {
            throw new \Magento\Framework\Exception\LocalizedException(__("The customer ID isn't defined."));
        }

        $quoteItemId = (int)$this->getRequest()->getParam('id');
        $websiteId = (int)$this->getRequest()->getParam('website_id');

        try {
            /** @var Item $quoteItem */
            $quoteItem = $this->quoteItemRetriever->getById($quoteItemId);
            $this->_quote = $this->quoteRepository->getForCustomer($this->_customerId, [$quoteItem->getStoreId()]);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->_quote = $this->quoteFactory->create();
        }
        $this->_quote->setWebsite(
            $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getWebsite($websiteId)
        );

        $this->_quoteItem = $this->_quote->getItemById($quoteItemId);
        if (!$this->_quoteItem) {
            throw new LocalizedException(__('The quote items are incorrect. Verify the quote items and try again.'));
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE)
            && $this->_authorization->isAllowed('Magento_Cart::cart');
    }
}
