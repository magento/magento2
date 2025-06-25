<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaymentGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Helper\Data as PaymentData;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;

/**
 * Checks if payment method is deferred (online)
 */
class IsDeferred implements ResolverInterface
{
    /**
     * @var PaymentData
     */
    private $paymentData;

    /**
     * @var array
     */
    private $overrides;

    /**
     * IsDeferred constructor.
     *
     * @param PaymentData $paymentData
     * @param array $overrides
     */
    public function __construct(
        PaymentData $paymentData,
        array $overrides = []
    ) {
        $this->paymentData = $paymentData;
        $this->overrides = $overrides;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!$value['code']) {
            throw new LocalizedException(__('"code" value should be specified'));
        }
        return $this->isDeferredPaymentMethod($value['code']);
    }

    /**
     * Identifies whether the payment method is deferred
     *
     * @param string $code
     *
     * @return bool
     */
    private function isDeferredPaymentMethod(string $code): bool
    {
        if (isset($this->overrides['deferred']) &&
            is_array($this->overrides['deferred']) &&
            in_array($code, $this->overrides['deferred'])
        ) {
            return true;
        }
        if (isset($this->overrides['undeferred']) &&
            is_array($this->overrides['undeferred']) &&
            in_array($code, $this->overrides['undeferred'])
        ) {
            return false;
        }
        return !$this->paymentData->getMethodInstance($code)->isOffline();
    }
}
