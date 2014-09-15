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
namespace Magento\Checkout\Service\V1\Data\Cart;

/**
 * Quote shipping method data
 *
 * @codeCoverageIgnore
 */
class ShippingMethodConverter
{
    /**
     * @var ShippingMethodBuilder
     */
    protected $builder;

    /**
     * @param ShippingMethodBuilder $builder
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Checkout\Service\V1\Data\Cart\ShippingMethodBuilder $builder,
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->builder = $builder;
        $this->storeManager = $storeManager;
    }

    /**
     * Convert rate model to ShippingMethod data object
     * @param string $quoteCurrencyCode
     * @param \Magento\Sales\Model\Quote\Address\Rate $rateModel
     *
     * @return \Magento\Checkout\Service\V1\Data\Cart\ShippingMethod
     */
    public function modelToDataObject($rateModel, $quoteCurrencyCode)
    {
        /** @var \Magento\Directory\Model\Currency $currency */
        $currency = $this->storeManager->getStore()->getBaseCurrency();

        $errorMessage = $rateModel->getErrorMessage();
        $data = [
            ShippingMethod::CARRIER_CODE => $rateModel->getCarrier(),
            ShippingMethod::METHOD_CODE => $rateModel->getMethod(),
            ShippingMethod::CARRIER_TITLE => $rateModel->getCarrierTitle(),
            ShippingMethod::METHOD_TITLE => $rateModel->getMethodTitle(),
            ShippingMethod::SHIPPING_AMOUNT => $currency->convert($rateModel->getPrice(), $quoteCurrencyCode),
            ShippingMethod::BASE_SHIPPING_AMOUNT => $rateModel->getPrice(),
            ShippingMethod::AVAILABLE => empty($errorMessage),
        ];
        $this->builder->populateWithArray($data);
        return $this->builder->create();
    }
}
