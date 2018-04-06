<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleGraphQlQuery\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface ItemInterface extends ExtensibleDataInterface
{
    const ENTITY_TYPE_CODE = 'test_graphql_item';

    /**
     * @return int
     */
    public function getItemId() : int;

    /**
     * @param int $itemId
     * @return ItemInterface
     */
    public function setItemId($itemId) : ItemInterface;

    /**
     * @return string
     */
    public function getName() : string;

    /**
     * @param string $name
     * @return ItemInterface
     */
    public function setName($name) : ItemInterface;

    /**
     * @return \Magento\TestModuleGraphQlQuery\Api\Data\ItemExtensionInterface|null
     */
    public function getExtensionAttributes() : ?\Magento\TestModuleGraphQlQuery\Api\Data\ItemExtensionInterface;

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\TestModuleGraphQlQuery\Api\Data\ItemExtensionInterface $extensionAttributes
     * @return ItemInterface
     */
    public function setExtensionAttributes(
        \Magento\TestModuleGraphQlQuery\Api\Data\ItemExtensionInterface $extensionAttributes
    ) : ItemInterface;
}
