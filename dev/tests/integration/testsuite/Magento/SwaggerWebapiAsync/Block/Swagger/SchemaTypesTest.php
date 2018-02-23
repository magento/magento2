<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SwaggerWebapiAsync\Block\Swagger;

/**
 * @magentoAppArea frontend
 */
class SchemaTypesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Swagger\Block\SchemaTypes
     */
    private $schemaTypes;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Framework\App\State::class)
            ->setAreaCode('frontend');

        $this->schemaTypes = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Swagger\Api\Block\SchemaTypesInterface::class
        );
    }

    /**
     * Test that the Swagger SchemaTypes contains the type added by SwaggerWebapiAsync.
     */
    public function testContainsSchemaType()
    {
        $schemaExists = function() {
            foreach ($this->schemaTypes->getTypes() as $schemaType) {
                // @todo: implement constant once merged with other bulk-api changes
                if ($schemaType->getCode() === 'async') {
                    return true;
                }
            }
            return false;
        };

        $this->assertTrue($schemaExists());
    }
}
