<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options;

/**
 * @magentoAppArea adminhtml
 */
class OptionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetOptionValuesCaching()
    {
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Options\Option::class
        );
        /** @var $productWithOptions \Magento\Catalog\Model\Product */
        $productWithOptions = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product::class
        );
        $productWithOptions->setTypeId(
            'simple'
        )->setId(
            1
        )->setAttributeSetId(
            4
        )->setWebsiteIds(
            [1]
        )->setName(
            'Simple Product With Custom Options'
        )->setSku(
            'simple'
        )->setPrice(
            10
        )->setMetaTitle(
            'meta title'
        )->setMetaKeyword(
            'meta keyword'
        )->setMetaDescription(
            'meta description'
        )->setVisibility(
            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH
        )->setStatus(
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        );

        $product = clone $productWithOptions;
        /** @var $option \Magento\Catalog\Model\Product\Option */
        $option = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product\Option::class,
            ['data' => ['id' => 1, 'title' => 'some_title']]
        );
        $productWithOptions->setOptions([$option]);
        $block->setProduct($productWithOptions);
        $this->assertNotEmpty($block->getOptionValues());

        $block->setProduct($product);
        $this->assertNotEmpty($block->getOptionValues());

        $block->setIgnoreCaching(true);
        $this->assertEmpty($block->getOptionValues());
    }
}
