<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Cart\Product\Composite;

use Magento\Backend\App\Action;
use Magento\Framework\Model\Exception;

/**
 * Catalog composite product configuration controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Cart extends \Magento\Backend\App\Action
{
    /**
     * Customer we're working with
     *
     * @var int id of the customer
     */
    protected $_customerId;

    /**
     * Quote we're working with
     *
     * @var \Magento\Sales\Model\Quote
     */
    protected $_quote = null;

    /**
     * Quote item we're working with
     *
     * @var \Magento\Sales\Model\Quote\Item
     */
    protected $_quoteItem = null;

    /**
     * @var \Magento\Sales\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @param Action\Context $context
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository
     */
    public function __construct(
        Action\Context $context,
        \Magento\Sales\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
        parent::__construct($context);
    }

    /**
     * Loads customer, quote and quote item by request params
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _initData()
    {
        $this->_customerId = (int)$this->getRequest()->getParam('customer_id');
        if (!$this->_customerId) {
            throw new \Magento\Framework\Model\Exception(__('No customer ID defined.'));
        }

        $quoteItemId = (int)$this->getRequest()->getParam('id');
        $websiteId = (int)$this->getRequest()->getParam('website_id');

        try {
            $this->_quote = $this->quoteRepository->getForCustomer($this->_customerId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->_quote = $this->quoteRepository->create();
        }
        $this->_quote->setWebsite(
            $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getWebsite($websiteId)
        );

        $this->_quoteItem = $this->_quote->getItemById($quoteItemId);
        if (!$this->_quoteItem) {
            throw new Exception(__('Please correct the quote items and try again.'));
        }

        return $this;
    }

    /**
     * Check the permission to Manage Customers
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Customer::manage');
    }
}
