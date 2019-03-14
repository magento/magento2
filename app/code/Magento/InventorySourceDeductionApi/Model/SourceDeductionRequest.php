<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceDeductionApi\Model;

use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;

/**
 * @inheritdoc
 */
class SourceDeductionRequest implements SourceDeductionRequestInterface
{
    /**
     * @var string
     */
    private $sourceCode;

    /**
     * @var ItemToDeductInterface[]
     */
    private $items;

    /**
     * @var SalesChannelInterface
     */
    private $salesChannel;

    /**
     * @var SalesEventInterface
     */
    private $salesEvent;

    /**
     * @param string $sourceCode
     * @param array $items
     * @param SalesChannelInterface $salesChannel
     * @param SalesEventInterface $salesEvent
     */
    public function __construct(
        string $sourceCode,
        array $items,
        SalesChannelInterface $salesChannel,
        SalesEventInterface $salesEvent
    ) {
        $this->sourceCode = $sourceCode;
        $this->items = $items;
        $this->salesChannel = $salesChannel;
        $this->salesEvent = $salesEvent;
    }

    /**
     * @inheritdoc
     */
    public function getSourceCode(): string
    {
        return $this->sourceCode;
    }

    /**
     * @inheritdoc
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function getSalesChannel(): SalesChannelInterface
    {
        return $this->salesChannel;
    }

    /**
     * @inheritdoc
     */
    public function getSalesEvent(): SalesEventInterface
    {
        return $this->salesEvent;
    }
}
