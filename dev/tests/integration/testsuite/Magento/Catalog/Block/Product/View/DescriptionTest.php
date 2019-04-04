<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\View;

use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea frontend
 */
class DescriptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Description
     */
    private $block;

    /**
     * @var Registry
     */
    private $registry;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->block = $objectManager->create(Description::class, [
            'data' => [
                'template' => 'Magento_Catalog::product/view/attribute.phtml'
            ]
        ]);

        $this->registry = $objectManager->get(Registry::class);
        $this->registry->unregister('product');
    }

    public function testGetProductWhenNoProductIsRegistered()
    {
        $html = $this->block->toHtml();
        $this->assertEmpty($html);
    }

    public function testGetProductWhenInvalidProductIsRegistered()
    {
        $this->registry->register('product', new \stdClass());
        $html = $this->block->toHtml();
        $this->assertEmpty($html);
    }
}
