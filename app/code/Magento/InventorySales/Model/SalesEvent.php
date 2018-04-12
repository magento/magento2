<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventorySalesApi\Api\Data\SalesEventInterface;

/**
 * @inheritdoc
 */
class SalesEvent implements SalesEventInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $objectId;

    public function __construct(string $type, int $objectId)
    {
        $this->type = $type;
        $this->objectId = $objectId;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getObjectId(): int
    {
        return $this->objectId;
    }
}
