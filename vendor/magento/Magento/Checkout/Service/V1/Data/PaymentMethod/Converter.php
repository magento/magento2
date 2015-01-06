<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Service\V1\Data\PaymentMethod;

use Magento\Checkout\Service\V1\Data\PaymentMethod as QuotePaymentMethod;

/**
 * Payment method converter.
 */
class Converter
{
    /**
     * Payment method builder.
     *
     * @var \Magento\Checkout\Service\V1\Data\Cart\PaymentMethodBuilder
     */
    protected $builder;

    /**
     * Constructs a payment method converter object.
     *
     * @param \Magento\Checkout\Service\V1\Data\PaymentMethodBuilder $builder Payment method builder.
     */
    public function __construct(\Magento\Checkout\Service\V1\Data\PaymentMethodBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Converts quote payment object to payment data object.
     *
     * @param \Magento\Payment\Model\MethodInterface $object The quote payment object.
     * @return \Magento\Checkout\Service\V1\Data\Cart\PaymentMethod Payment data object.
     */
    public function toDataObject(\Magento\Payment\Model\MethodInterface $object)
    {
        $data = [
            QuotePaymentMethod::CODE => $object->getCode(),
            QuotePaymentMethod::TITLE => $object->getTitle(),
        ];
        return $this->builder->populateWithArray($data)->create();
    }
}
