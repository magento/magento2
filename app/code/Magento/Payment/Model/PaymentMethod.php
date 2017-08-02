<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

/**
 * Payment method class.
 * @since 2.1.3
 */
class PaymentMethod implements \Magento\Payment\Api\Data\PaymentMethodInterface
{
    /**
     * @var string
     * @since 2.1.3
     */
    private $code;

    /**
     * @var string
     * @since 2.1.3
     */
    private $title;

    /**
     * @var int
     * @since 2.1.3
     */
    private $storeId;

    /**
     * @var bool
     * @since 2.1.3
     */
    private $isActive;

    /**
     * @param string $code
     * @param string $title
     * @param int $storeId
     * @param bool $isActive
     * @since 2.1.3
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
     * @since 2.1.3
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.3
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.3
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.3
     */
    public function getIsActive()
    {
        return $this->isActive;
    }
}
