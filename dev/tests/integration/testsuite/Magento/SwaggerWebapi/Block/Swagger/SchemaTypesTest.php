<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SwaggerWebapi\Block\Swagger;

use Magento\Swagger\Api\Block\SchemaTypesInterface;

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
            SchemaTypesInterface::class
        );
    }

    /**
     * Test that the Swagger SchemaTypes contains the type added by SwaggerWebapi.
     */
    public function testContainsSchemaType()
    {
        $schemaExists = function () {
            foreach ($this->schemaTypes->getTypes() as $schemaType) {
                if ($schemaType->getCode() === 'rest') {
                    return true;
                }
            }
            return false;
        };

        $this->assertTrue($schemaExists());
    }
}
