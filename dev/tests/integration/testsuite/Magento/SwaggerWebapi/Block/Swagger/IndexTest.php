<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SwaggerWebapi\Block\Swagger;

/**
 * @magentoAppArea frontend
 */
class IndexTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Swagger\Block\Index
     */
    private $block;

    protected function setUp(): void
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
                    'schema_types' => [
                        'rest' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                            \Magento\SwaggerWebapi\Model\SchemaType\Rest::class
                        )
                    ],
                    'default_schema_type_code' => 'rest'
                ]
            ]
        );
    }

    /**
     * Test that the Swagger UI outputs rest as the default when there is no type parameter supplied via URL.
     */
    public function testDefaultSchemaUrlOutput()
    {
        $this->assertStringEndsWith('/rest/all/schema?services=all', $this->block->getSchemaUrl());
    }

    /**
     * Test that Swagger UI outputs the supplied store code when it is specified.
     */
    public function testSchemaUrlOutputWithStore()
    {
        $this->block->getRequest()->setParams([
            'store' => 'custom',
        ]);

        $this->assertStringEndsWith('/rest/custom/schema?services=all', $this->block->getSchemaUrl());
    }
}
