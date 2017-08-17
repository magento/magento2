<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Category;

/**
 * @magentoAppArea adminhtml
 */
class TreeTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Catalog\Block\Adminhtml\Category\Tree */
    protected $_block;

    protected function setUp()
    {
        parent::setUp();
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Block\Adminhtml\Category\Tree::class
        );

        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Catalog\Block\Adminhtml\Category\Tree::class,
            '',
            []
        );
    }

    public function testGetSuggestedCategoriesJson()
    {
        $this->assertEquals(
            '[{"id":"2","children":[],"is_active":"1","label":"Default Category"}]',
            $this->_block->getSuggestedCategoriesJson('Default')
        );
        $this->assertEquals('[]', $this->_block->getSuggestedCategoriesJson(strrev('Default')));
    }
}
