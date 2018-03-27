<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SwaggerWebapiAsync\Block\Swagger;

use Magento\Swagger\Api\Data\SchemaTypeInterface;

/**
 * @magentoAppArea frontend
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Swagger\Block\Index
     */
    private $block;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\App\State::class)
            ->setAreaCode('frontend');

        $this->block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Swagger\Block\Index::class,
            '',
            [
                'data' => [
                    'schema_types' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                        SchemaTypeInterface::class
                    )
                ]
            ]
        );
    }

    /**
     * Test that Swagger UI outputs the all store code when it is specified.
     */
    public function testSchemaUrlOutput()
    {
        $this->block->getRequest()->setParams([
            'type' => 'async',
            'store' => 'custom',
        ]);

        $this->assertStringEndsWith('/async/all/schema?services=all', $this->block->getSchemaUrl());
    }

    /**
     * Test that Swagger UI outputs the supplied store code when it is specified.
     */
    public function testSchemaUrlOutputWithStore()
    {
        $this->block->getRequest()->setParams([
            'type' => 'async',
            'store' => 'custom',
        ]);

        $this->assertStringEndsWith('/async/custom/schema?services=all', $this->block->getSchemaUrl());
    }
}
