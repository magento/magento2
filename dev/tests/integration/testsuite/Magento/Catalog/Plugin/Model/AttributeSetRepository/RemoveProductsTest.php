<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Plugin\Model\AttributeSetRepository;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Interception\PluginList;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for RemoveProducts plugin.
 * @magentoAppArea adminhtml
 */
class RemoveProductsTest extends TestCase
{
    /**
     * @return void
     */
    public function testRemoveProductsIsRegistered()
    {
        $pluginInfo = Bootstrap::getObjectManager()->get(PluginList::class)
            ->get(AttributeSetRepositoryInterface::class, []);
        self::assertSame(RemoveProducts::class, $pluginInfo['remove_products']['instance']);
    }

    /**
     * Test related to given attribute set products will be removed, if attribute set will be deleted.
     *
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_with_product.php
     */
    public function testAfterDelete()
    {
        $attributeSet = Bootstrap::getObjectManager()->get(Set::class);
        $attributeSet->load('empty_attribute_set', 'attribute_set_name');

        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');

        $productCollection = Bootstrap::getObjectManager()->get(CollectionFactory::class)->create();
        $productCollection->addIdFilter($product->getId());
        $urlRewriteCollection = Bootstrap::getObjectManager()->get(UrlRewriteCollectionFactory::class)->create();
        $urlRewriteCollection->addFieldToFilter('entity_type', 'product');
        $urlRewriteCollection->addFieldToFilter('entity_id', $product->getId());

        self::assertSame(1, $urlRewriteCollection->getSize());
        self::assertSame(1, $productCollection->getSize());

        $attributeSetRepository = Bootstrap::getObjectManager()->get(AttributeSetRepositoryInterface::class);
        $attributeSetRepository->deleteById($attributeSet->getAttributeSetId());

        $productCollection = Bootstrap::getObjectManager()->get(CollectionFactory::class)->create();
        $productCollection->addIdFilter($product->getId());
        $urlRewriteCollection = Bootstrap::getObjectManager()->get(UrlRewriteCollectionFactory::class)->create();
        $urlRewriteCollection->addFieldToFilter('entity_type', 'product');
        $urlRewriteCollection->addFieldToFilter('entity_id', $product->getId());

        self::assertSame(0, $urlRewriteCollection->getSize());
        self::assertSame(0, $productCollection->getSize());
    }
}
