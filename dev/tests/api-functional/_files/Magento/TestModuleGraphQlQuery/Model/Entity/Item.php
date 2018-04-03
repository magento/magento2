<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleGraphQlQuery\Model\Entity;

use Magento\TestModuleGraphQlQuery\Api\Data\ItemInterface;
use Magento\Framework\Api\ExtensionAttributesFactory;

class Item extends \Magento\Framework\Api\AbstractExtensibleObject implements ItemInterface
{
    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionAttributesFactory;

    /**
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     */
    public function __construct(ExtensionAttributesFactory $extensionAttributesFactory)
    {
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    /**
     * @return int
     */
    public function getItemId() : int
    {
        return $this->_data['item_id'];
    }

    /**
     * @param int $itemId
     * @return ItemInterface
     */
    public function setItemId($itemId) : ItemInterface
    {
        return $this->setData('item_id', $itemId);
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->_data['name'];
    }

    /**
     * @param string $name
     * @return ItemInterface
     */
    public function setName($name) : ItemInterface
    {
        return $this->setData('name', $name);
    }

    public function getExtensionAttributes() : ?\Magento\TestModuleGraphQlQuery\Api\Data\ItemExtensionInterface
    {
        $extensionAttributes = $this->_getExtensionAttributes();
        if (null === $extensionAttributes) {
            /** @var \Magento\TestModuleGraphQlQuery\Api\Data\ItemExtensionInterface $extensionAttributes */
            $extensionAttributes = $this->extensionAttributesFactory->create(ItemInterface::class);
            $this->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    public function setExtensionAttributes(
        \Magento\TestModuleGraphQlQuery\Api\Data\ItemExtensionInterface $extensionAttributes
    ) : ItemInterface {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
