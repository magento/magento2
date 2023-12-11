<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;

class ProductAttributeManagementTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductAttributeManagementV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products/attribute-sets';

    public function testGetAttributes()
    {
        $attributeSetId = 4;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $attributeSetId . '/attributes',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetAttributes',
            ],
        ];
        $attributes = $this->_webApiCall($serviceInfo, ['attributeSetId' => $attributeSetId]);

        $this->assertTrue(count($attributes) > 0);
        $this->assertArrayHasKey('attribute_code', $attributes[0]);
        $this->assertArrayHasKey('attribute_id', $attributes[0]);
        $this->assertArrayHasKey('default_frontend_label', $attributes[0]);
        $this->assertNotNull($attributes[0]['attribute_code']);
        $this->assertNotNull($attributes[0]['attribute_id']);
        $this->assertNotNull($attributes[0]['default_frontend_label']);
    }

    public function testAssignAttribute()
    {
        $this->assertNotNull(
            $this->_webApiCall(
                $this->getAssignServiceInfo(),
                $this->getAttributeData()
            )
        );
    }

    public function testAssignAttributeWrongAttributeSet()
    {
        $payload = $this->getAttributeData();
        $payload['attributeSetId'] = -1;

        $expectedMessage = 'The AttributeSet with a "%1" ID doesn\'t exist. Verify the attributeSet and try again.';

        try {
            $this->_webApiCall($this->getAssignServiceInfo(), $payload);
            $this->fail("Expected exception");
        } catch (\SoapFault $e) {
            $this->assertStringContainsString(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals([$payload['attributeSetId']], $errorObj['parameters']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
        }
    }

    public function testAssignAttributeWrongAttributeGroup()
    {
        $payload = $this->getAttributeData();
        $payload['attributeGroupId'] = -1;
        $expectedMessage = 'The group with the "%1" ID doesn\'t exist. Verify the ID and try again.';

        try {
            $this->_webApiCall($this->getAssignServiceInfo(), $payload);
            $this->fail("Expected exception");
        } catch (\SoapFault $e) {
            $this->assertStringContainsString(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals([$payload['attributeGroupId']], $errorObj['parameters']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
        }
    }

    public function testAssignAttributeWrongAttribute()
    {
        $payload = $this->getAttributeData();
        $payload['attributeCode'] = 'badCode';
        $expectedMessage =
            'The attribute with a "%1" attributeCode doesn\'t exist. Verify the attribute and try again.';

        try {
            $this->_webApiCall($this->getAssignServiceInfo(), $payload);
            $this->fail("Expected exception");
        } catch (\SoapFault $e) {
            $this->assertStringContainsString(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals($expectedMessage, $errorObj['message']);
            $this->assertEquals([$payload['attributeCode']], $errorObj['parameters']);
            $this->assertEquals(HTTPExceptionCodes::HTTP_NOT_FOUND, $e->getCode());
        }
    }

    public function testUnassignAttribute()
    {
        $payload = $this->getAttributeData();

        //Assign attribute to attribute set
        /** @var \Magento\Eav\Model\AttributeManagement $attributeManagement */
        $attributeManagement = Bootstrap::getObjectManager()->get(\Magento\Eav\Model\AttributeManagement::class);
        $attributeManagement->assign(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $payload['attributeSetId'],
            $payload['attributeGroupId'],
            $payload['attributeCode'],
            $payload['sortOrder']
        );

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH .
                    '/' . $payload['attributeSetId'] .
                    '/attributes/' .
                    $payload['attributeCode'],
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Unassign',
            ],
        ];
        $this->assertTrue(
            $this->_webApiCall(
                $serviceInfo,
                [
                    'attributeSetId' => $payload['attributeSetId'],
                    'attributeCode' => $payload['attributeCode'],
                ]
            )
        );
    }

    protected function getAttributeData()
    {
        return [
            'attributeSetId' => 4,
            'attributeGroupId' => 8,
            'attributeCode' => 'cost',
            'sortOrder' => 3,
        ];
    }

    protected function getAssignServiceInfo()
    {
        return [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/attributes',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Assign',
            ],
        ];
    }
}
