<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Api;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;
use Magento\Framework\Webapi\Exception as HTTPExceptionCodes;

class AttributeSetManagementTest extends WebapiAbstract
{
    /**
     * @var array
     */
    private $createServiceInfo;

    protected function setUp()
    {
        $this->createServiceInfo = [
            'rest' => [
                'resourcePath' => '/V1/eav/attribute-sets',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => 'eavAttributeSetManagementV1',
                'serviceVersion' => 'V1',
                'operation' => 'eavAttributeSetManagementV1Create',
            ],
        ];
    }

    public function testCreate()
    {
        $entityTypeCode = 'catalog_product';
        $entityType = $this->getEntityTypeByCode($entityTypeCode);
        $attributeSetName = 'new_attribute_set';

        $arguments = [
            'entityTypeCode' => $entityTypeCode,
            'attributeSet' => [
                'attribute_set_name' => $attributeSetName,
                'sort_order' => 500,
            ],
            'skeletonId' => $entityType->getDefaultAttributeSetId(),
        ];
        $result = $this->_webApiCall($this->createServiceInfo, $arguments);
        $this->assertNotNull($result);
        $attributeSet = $this->getAttributeSetByName($attributeSetName);
        $this->assertNotNull($attributeSet);
        $this->assertEquals($attributeSet->getId(), $result['attribute_set_id']);
        $this->assertEquals($attributeSet->getAttributeSetName(), $result['attribute_set_name']);
        $this->assertEquals($attributeSet->getEntityTypeId(), $result['entity_type_id']);
        $this->assertEquals($attributeSet->getEntityTypeId(), $entityType->getId());
        $this->assertEquals($attributeSet->getSortOrder(), $result['sort_order']);
        $this->assertEquals($attributeSet->getSortOrder(), 500);

        // Clean up database
        $attributeSet->delete();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid value
     */
    public function testCreateThrowsExceptionIfGivenAttributeSetAlreadyHasId()
    {
        $entityTypeCode = 'catalog_product';
        $entityType = $this->getEntityTypeByCode($entityTypeCode);
        $attributeSetName = 'new_attribute_set';

        $arguments = [
            'entityTypeCode' => $entityTypeCode,
            'attributeSet' => [
                'attribute_set_id' => 1,
                'attribute_set_name' => $attributeSetName,
                'sort_order' => 100,
            ],
            'skeletonId' => $entityType->getDefaultAttributeSetId(),
        ];
        $this->_webApiCall($this->createServiceInfo, $arguments);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid value
     */
    public function testCreateThrowsExceptionIfGivenSkeletonIdIsInvalid()
    {
        $entityTypeCode = 'catalog_product';
        $attributeSetName = 'new_attribute_set';

        $arguments = [
            'entityTypeCode' => $entityTypeCode,
            'attributeSet' => [
                'attribute_set_name' => $attributeSetName,
                'sort_order' => 200,
            ],
            'skeletonId' => 0,
        ];
        $this->_webApiCall($this->createServiceInfo, $arguments);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No such entity
     */
    public function testCreateThrowsExceptionIfGivenSkeletonAttributeSetDoesNotExist()
    {
        $attributeSetName = 'new_attribute_set';
        $entityTypeCode = 'catalog_product';

        $arguments = [
            'entityTypeCode' => $entityTypeCode,
            'attributeSet' => [
                'attribute_set_name' => $attributeSetName,
                'sort_order' => 300,
            ],
            'skeletonId' => 9999,
        ];
        $this->_webApiCall($this->createServiceInfo, $arguments);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid entity_type specified: invalid_entity_type
     */
    public function testCreateThrowsExceptionIfGivenEntityTypeDoesNotExist()
    {
        $entityTypeCode = 'catalog_product';
        $entityType = $this->getEntityTypeByCode($entityTypeCode);
        $attributeSetName = 'new_attribute_set';

        $arguments = [
            'entityTypeCode' => 'invalid_entity_type',
            'attributeSet' => [
                'attribute_set_name' => $attributeSetName,
                'sort_order' => 400,
            ],
            'skeletonId' => $entityType->getDefaultAttributeSetId(),
        ];
        $this->_webApiCall($this->createServiceInfo, $arguments);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage The attribute set name is empty. Enter the name and try again.
     */
    public function testCreateThrowsExceptionIfAttributeSetNameIsEmpty()
    {
        $entityTypeCode = 'catalog_product';
        $entityType = $this->getEntityTypeByCode($entityTypeCode);
        $attributeSetName = '';

        $arguments = [
            'entityTypeCode' => $entityTypeCode,
            'attributeSet' => [
                'attribute_set_name' => $attributeSetName,
                'sort_order' => 500,
            ],
            'skeletonId' => $entityType->getDefaultAttributeSetId(),
        ];
        $this->_webApiCall($this->createServiceInfo, $arguments);
    }

    public function testCreateThrowsExceptionIfAttributeSetWithGivenNameAlreadyExists()
    {
        $entityTypeCode = 'catalog_product';
        $entityType = $this->getEntityTypeByCode($entityTypeCode);
        $attributeSetName = 'Default';
        $expectedMessage = 'A "Default" attribute set name already exists. Create a new name and try again.';

        $arguments = [
            'entityTypeCode' => $entityTypeCode,
            'attributeSet' => [
                'attribute_set_name' => $attributeSetName,
                'sort_order' => 550,
            ],
            'skeletonId' => $entityType->getDefaultAttributeSetId(),
        ];

        try {
            $this->_webApiCall($this->createServiceInfo, $arguments);
            $this->fail("Expected exception");
        } catch (\SoapFault $e) {
            $this->assertContains(
                $expectedMessage,
                $e->getMessage(),
                "SoapFault does not contain expected message."
            );
        } catch (\Exception $e) {
            $errorObj = $this->processRestExceptionResult($e);
            $this->assertEquals(
                $expectedMessage,
                $errorObj['message']
            );
            $this->assertEquals(HTTPExceptionCodes::HTTP_BAD_REQUEST, $e->getCode());
        }
    }

    /**
     * Retrieve attribute set based on given name.
     * This utility methods assumes that there is only one attribute set with given name,
     *
     * @param string $attributeSetName
     * @return \Magento\Eav\Model\Entity\Attribute\Set|null
     */
    protected function getAttributeSetByName($attributeSetName)
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
        $attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class)
            ->load($attributeSetName, 'attribute_set_name');
        if ($attributeSet->getId() === null) {
            return null;
        }
        return $attributeSet;
    }

    /**
     * Retrieve entity type based on given code.
     *
     * @param string $entityTypeCode
     * @return \Magento\Eav\Model\Entity\Type|null
     */
    protected function getEntityTypeByCode($entityTypeCode)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Eav\Model\Entity\Type $entityType */
        $entityType = $objectManager->create(\Magento\Eav\Model\Config::class)
            ->getEntityType($entityTypeCode);
        return $entityType;
    }
}
