<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Model\Quote\Item;

use Magento\GroupedProduct\Api\Data\GroupedOptionsInterface;
use Magento\GroupedProduct\Api\Data\GroupedOptionsExtensionInterface;

/**
 * @inheritDoc
 */
class GroupedOptions implements GroupedOptionsInterface
{
    /**
     * @var float
     */
    private $qty;

    /**
     * @var int
     */
    private $id;

    /**
     * @var GroupedOptionsExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param int $id
     * @param float $qty
     * @param GroupedOptionsExtensionInterface|null $extensionAttributes
     */
    public function __construct(int $id, float $qty, $extensionAttributes = null)
    {
        $this->id = $id;
        $this->qty = $qty;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getQty(): float
    {
        return $this->qty;
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(GroupedOptionsExtensionInterface $extensionAttributes): void
    {
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?GroupedOptionsExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
