<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model;

use \Magento\Framework\Exception\NoSuchEntityException;

class QuoteRepository
{
    /**
     * @var \Magento\Sales\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param QuoteFactory $quoteFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Get quote by id
     *
     * @param int $cartId
     * @return Quote
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($cartId)
    {
        return $this->loadQuote('load', 'cartId', $cartId);
    }


    /**
     * Get quote by customer Id
     *
     * @param int $customerId
     * @return Quote
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getForCustomer($customerId)
    {
        return $this->loadQuote('loadByCustomer', 'customerId', $customerId);
    }

    /**
     * Load quote with different methods
     *
     * @param string $loadMethod
     * @param string $loadField
     * @param int $identifier
     * @return Quote
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function loadQuote($loadMethod, $loadField, $identifier)
    {
        $quote = $this->quoteFactory->create();
        $quote->setStoreId($this->storeManager->getStore()->getId())->$loadMethod($identifier);
        if (!$quote->getId() || !$quote->getIsActive()) {
            throw NoSuchEntityException::singleField($loadField, $identifier);
        }
        return $quote;
    }
}
