<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Payment\Model;

/**
 * Payment method class.
 */
class PaymentMethod implements \Magento\Payment\Api\Data\PaymentMethodInterface
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $title;

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var bool
     */
    private $isActive;

    /**
     * @param string $code
     * @param string $title
     * @param int $storeId
     * @param bool $isActive
     */
    public function __construct($code, $title, $storeId, $isActive)
    {
        $this->code = $code;
        $this->title = $title;
        $this->storeId = $storeId;
        $this->isActive = $isActive;
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->isActive;
    }
}
