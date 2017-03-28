<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Cart\Product\Composite;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;

/**
 * Catalog composite product configuration controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Cart extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Customer::manage';

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
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $_quoteItem = null;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     */
    public function __construct(
        Action\Context $context,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->quoteFactory = $quoteFactory;
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
            throw new \Magento\Framework\Exception\LocalizedException(__('No customer ID defined.'));
        }

        $quoteItemId = (int)$this->getRequest()->getParam('id');
        $websiteId = (int)$this->getRequest()->getParam('website_id');

        try {
            $this->_quote = $this->quoteRepository->getForCustomer($this->_customerId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->_quote = $this->quoteFactory->create();
        }
        $this->_quote->setWebsite(
            $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getWebsite($websiteId)
        );

        $this->_quoteItem = $this->_quote->getItemById($quoteItemId);
        if (!$this->_quoteItem) {
            throw new LocalizedException(__('Please correct the quote items and try again.'));
        }

        return $this;
    }
}
