<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Api\ExtensionAttribute;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerExtensionInterface;

/**
 * Class to test the automatic generation of extension attributes object.
 */
class ExtensionAttributesGenerationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test extension attributes generation for extensible models.
     *
     * Make sure that extension attributes object is not empty after instantiation
     * of objects inherited from @see \Magento\Framework\Model\AbstractExtensibleModel.
     *
     * In addition, verify that empty objects are not generated for complex extension attributes.
     */
    public function testAttributeObjectGenerationForExtensibleModel()
    {
        /** @var \Magento\Framework\ObjectManagerInterface */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var ProductInterface $product */
        $product = $objectManager->get(ProductInterface::class);

        $extensionAttributes = $product->getExtensionAttributes();
        $this->assertInstanceOf(ProductExtensionInterface::class, $extensionAttributes);

        $stockItemExtensionAttribute = $extensionAttributes->getStockItem();
        $this->assertNull($stockItemExtensionAttribute);
    }

    /**
     * Test extension attributes generation for extensible objects.
     *
     * Make sure that extension attributes object is not empty after instantiation
     * of objects inherited from @see \Magento\Framework\Api\AbstractExtensibleObject
     */
    public function testAttributeObjectGenerationForExtensibleObject()
    {
        /** @var \Magento\Framework\ObjectManagerInterface */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var CustomerInterface $customer */
        $customer = $objectManager->get(CustomerInterface::class);

        $extensionAttributes = $customer->getExtensionAttributes();
        $this->assertInstanceOf(CustomerExtensionInterface::class, $extensionAttributes);
    }
}
