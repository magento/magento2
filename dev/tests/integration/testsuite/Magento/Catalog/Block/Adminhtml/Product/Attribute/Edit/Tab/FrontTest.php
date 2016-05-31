<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab;

/**
 * @magentoAppArea adminhtml
 */
class FrontTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front
     */
    private $block;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $layout \Magento\Framework\View\Layout */
        $layout = $this->objectManager->create('Magento\Framework\View\LayoutInterface');
        $this->block = $layout->createBlock('Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front');
    }

    /**
     * @param $attributeCode
     * @dataProvider toHtmlDataProvider
     */
    public function testToHtml($attributeCode)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $model */
        $model = $this->objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute');
        $model->loadByCode(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);

        /** @var \Magento\Framework\Registry $coreRegistry */
        $coreRegistry = $this->objectManager->get('\Magento\Framework\Registry');
        $coreRegistry->unregister('entity_attribute');
        $coreRegistry->register('entity_attribute', $model);

        $this->assertRegExp('/<select\sid="is_searchable".*disabled="disabled"/', $this->block->toHtml());
    }

    /**
     * @return array
     */
    public function toHtmlDataProvider()
    {
        return [
            ['visibility'],
            ['url_key'],
            ['status'],
            ['price_type'],
            ['category_ids'],
            ['media_gallery'],
            ['country_of_manufacture'],
        ];
    }
}
