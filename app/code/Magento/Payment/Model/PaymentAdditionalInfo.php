<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Payment\Model;

use Magento\Payment\Api\Data\PaymentAdditionalInfoInterface;

/**
 * Payment additional info class.
 */
class PaymentAdditionalInfo implements PaymentAdditionalInfoInterface
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $value;

    /**
     * @inheritdoc
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $key;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $value;
    }
}
