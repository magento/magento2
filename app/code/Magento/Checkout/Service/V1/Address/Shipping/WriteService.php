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

namespace Magento\Checkout\Service\V1\Address\Shipping;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Logger;

class WriteService implements WriteServiceInterface
{
    /**
     * @var \Magento\Checkout\Service\V1\QuoteLoader
     */
    protected $quoteLoader;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Sales\Model\Quote\AddressFactory
     */
    protected $quoteAddressFactory;

    /**
     * @var \Magento\Checkout\Service\V1\Address\Converter
     */
    protected $addressConverter;

    /**
     * @var \Magento\Checkout\Service\V1\Address\Validator
     */
    protected $addressValidator;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param \Magento\Checkout\Service\V1\QuoteLoader $quoteLoader
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Service\V1\Address\Converter $addressConverter
     * @param \Magento\Checkout\Service\V1\Address\Validator $addressValidator
     * @param \Magento\Sales\Model\Quote\AddressFactory $quoteAddressFactory
     * @param Logger $logger
     */
    public function __construct(
        \Magento\Checkout\Service\V1\QuoteLoader $quoteLoader,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Service\V1\Address\Converter $addressConverter,
        \Magento\Checkout\Service\V1\Address\Validator $addressValidator,
        \Magento\Sales\Model\Quote\AddressFactory $quoteAddressFactory,
        Logger $logger
    ) {
        $this->quoteLoader = $quoteLoader;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->addressConverter = $addressConverter;
        $this->addressValidator = $addressValidator;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function setAddress($cartId, $addressData)
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->quoteLoader->load($cartId, $this->storeManager->getStore()->getId());
        if ($quote->isVirtual()) {
            throw new NoSuchEntityException(
                'Cart contains virtual product(s) only. Shipping address is not applicable'
            );
        }
        /** @var \Magento\Sales\Model\Quote\Address $address */
        $address = $this->quoteAddressFactory->create();
        $this->addressValidator->validate($addressData);
        if ($addressData->getId()) {
            $address->load($addressData->getId());
        }
        $address = $this->addressConverter->convertDataObjectToModel($addressData, $address);
        $address->setSameAsBilling(0);
        $quote->setShippingAddress($address);
        $quote->setDataChanges(true);
        try {
            $quote->save();
        } catch (\Exception $e) {
            $this->logger->logException($e);
            throw new InputException('Unable to save address. Please, check input data.');
        }
        return $quote->getShippingAddress()->getId();
    }
}
