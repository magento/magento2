<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Block\Product\View;

use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea frontend
 */
class DescriptionTest extends TestCase
{
    /**
     * @var Description
     */
    private $block;

    /**
     * @var Registry
     */
    private $registry;

    protected function setUp(): void
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
