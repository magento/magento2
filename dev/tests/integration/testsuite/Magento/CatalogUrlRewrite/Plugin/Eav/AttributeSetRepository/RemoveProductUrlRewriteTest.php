<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogUrlRewrite\Plugin\Eav\AttributeSetRepository;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Interception\PluginList;
use Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for RemoveProductUrlRewrite plugin.
 * @magentoAppArea adminhtml
 */
class RemoveProductUrlRewriteTest extends TestCase
{
    /**
     * @return void
     */
    public function testRemoveProductUrlRewriteIsRegistered()
    {
        $pluginInfo = Bootstrap::getObjectManager()->get(PluginList::class)
            ->get(AttributeSetRepositoryInterface::class, []);
        self::assertSame(RemoveProductUrlRewrite::class, $pluginInfo['attribute_set_delete_plugin']['instance']);
    }

    /**
     * Test url rewrite will be removed for product with given attribute set, if one will be deleted.
     *
     * @magentoDataFixture Magento/CatalogUrlRewrite/_files/attribute_set_with_product.php
     * @magentoDbIsolation enabled
     */
    public function testAroundDelete()
    {
        $attributeSet = Bootstrap::getObjectManager()->get(Set::class);
        $attributeSet->load('empty_attribute_set', 'attribute_set_name');

        $productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');

        $urlRewriteCollection = Bootstrap::getObjectManager()->get(UrlRewriteCollectionFactory::class)->create();
        $urlRewriteCollection->addFieldToFilter('entity_type', 'product');
        $urlRewriteCollection->addFieldToFilter('entity_id', $product->getId());

        self::assertSame(1, $urlRewriteCollection->getSize());

        $attributeSetRepository = Bootstrap::getObjectManager()->get(AttributeSetRepositoryInterface::class);
        $attributeSetRepository->deleteById($attributeSet->getAttributeSetId());

        $urlRewriteCollection = Bootstrap::getObjectManager()->get(UrlRewriteCollectionFactory::class)->create();
        $urlRewriteCollection->addFieldToFilter('entity_type', 'product');
        $urlRewriteCollection->addFieldToFilter('entity_id', $product->getId());

        self::assertSame(0, $urlRewriteCollection->getSize());
    }
}
