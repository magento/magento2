<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Entity;

class HydratorTest extends \PHPUnit_Framework_TestCase
{
    const CUSTOM_ATTRIBUTE_CODE = 'description';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    public function testExtractAndHydrate()
    {
        /** @var \Magento\Framework\EntityManager\Hydrator $hydrator */
        $hydrator = $this->objectManager->create(\Magento\Framework\EntityManager\Hydrator::class);

        /** @var \Magento\Framework\Api\AttributeInterface $customAttribute */
        $customAttribute = $this->objectManager->create(\Magento\Framework\Api\AttributeInterface::class);
        $customAttribute->setAttributeCode(self::CUSTOM_ATTRIBUTE_CODE)
            ->setValue('Product description');

        /** @var \Magento\CatalogInventory\Api\Data\StockItemInterface $extensionAttribute */
        $stockItem = $this->objectManager->create(
            \Magento\CatalogInventory\Api\Data\StockItemInterface::class
        );
        $stockItem->setProductId(1)
            ->setQty(100);

        /** @var \Magento\Catalog\Api\Data\ProductExtension $productExtension */
        $productExtension = $this->objectManager->create(\Magento\Catalog\Api\Data\ProductExtension::class);
        $productExtension->setStockItem($stockItem);

        /** @var \Magento\Catalog\Api\Data\ProductLinkInterface $productLink */
        $productLink = $this->objectManager->create(\Magento\Catalog\Api\Data\ProductLinkInterface::class);
        $productLink->setSku('sku')
            ->setLinkedProductSku('linked-sku');

        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        $product = $this->objectManager->create(\Magento\Catalog\Api\Data\ProductInterface::class);
        $product->setSku('sku')
            ->setName('Product name')
            ->setCustomAttributes([self::CUSTOM_ATTRIBUTE_CODE => $customAttribute])
            ->setExtensionAttributes($productExtension)
            ->setProductLinks([$productLink]);

        $productData = $hydrator->extract($product);
        /** @var \Magento\Catalog\Api\Data\ProductInterface $newProduct */
        $newProduct = $this->objectManager->create(\Magento\Catalog\Api\Data\ProductInterface::class);
        $newProduct = $hydrator->hydrate($newProduct, $productData);
        $newProductData = $hydrator->extract($newProduct);

        $this->assertEquals($productData, $newProductData);
    }
}
