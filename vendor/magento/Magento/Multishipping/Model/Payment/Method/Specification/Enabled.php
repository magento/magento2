<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Multishipping\Model\Payment\Method\Specification;

use Magento\Payment\Model\Method\Specification\AbstractSpecification;

/**
 * Enable method specification
 */
class Enabled extends AbstractSpecification
{
    /**
     * Allow multiple address flag
     */
    const FLAG_ALLOW_MULTIPLE_ADDRESS = 'allow_multiple_address';

    /**
     * {@inheritdoc}
     */
    public function isSatisfiedBy($paymentMethod)
    {
        return isset(
            $this->methodsInfo[$paymentMethod][self::FLAG_ALLOW_MULTIPLE_ADDRESS]
        ) && $this->methodsInfo[$paymentMethod][self::FLAG_ALLOW_MULTIPLE_ADDRESS];
    }
}
