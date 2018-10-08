<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\InventorySalesApi\Api\Data\SalesEventExtensionInterface;

/**
 * @inheritdoc
 */
class SalesEvent extends AbstractExtensibleModel implements SalesEventInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $objectType;

    /**
     * @var string
     */
    private $objectId;

    /**
     * @param string $type
     * @param string $objectType
     * @param string $objectId
     */
    public function __construct(string $type, string $objectType, string $objectId)
    {
        $this->type = $type;
        $this->objectType = $objectType;
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
    public function getObjectType(): string
    {
        return $this->objectType;
    }

    /**
     * @inheritdoc
     */
    public function getObjectId(): string
    {
        return $this->objectId;
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->extensionAttributesFactory->create(SalesEventInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(SalesEventExtensionInterface $extensionAttributes)
    {
        $this->_setExtensionAttributes($extensionAttributes);
    }
}
