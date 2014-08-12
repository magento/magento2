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

namespace Magento\Checkout\Service\V1\Address\Billing;

use \Magento\Checkout\Service\V1\Address\Converter as AddressConverter;

class ReadService implements ReadServiceInterface
{
    /**
     * @var \Magento\Checkout\Service\V1\QuoteLoader
     */
    protected $quoteLoader;

    /**
     * @var AddressConverter
     */
    protected $addressConverter;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Checkout\Service\V1\QuoteLoader $quoteLoader
     * @param AddressConverter $addressConverter
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Checkout\Service\V1\QuoteLoader $quoteLoader,
        AddressConverter $addressConverter,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->quoteLoader = $quoteLoader;
        $this->addressConverter = $addressConverter;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress($cartId)
    {
        $storeId = $this->storeManager->getStore()->getId();

        /** @var  \Magento\Sales\Model\Quote\Address $address */
        $address = $this->quoteLoader->load($cartId, $storeId)->getBillingAddress();
        return $this->addressConverter->convertModelToDataObject($address);
    }
}
