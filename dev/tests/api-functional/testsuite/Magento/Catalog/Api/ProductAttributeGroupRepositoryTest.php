<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Api;

use Magento\Webapi\Model\Rest\Config as RestConfig;

class ProductAttributeGroupRepositoryTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    const SERVICE_NAME = 'catalogProductAttributeGroupRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/products/attribute-sets';

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/empty_attribute_group.php
     */
    public function testCreateGroup()
    {
        $attributeSetId = 1;
        $groupData = $this->createGroupData($attributeSetId);
        $groupData['attribute_group_name'] = 'empty_attribute_group_updated';

        $result = $this->createGroup($attributeSetId, $groupData);
        $this->assertArrayHasKey('attribute_group_id', $result);
        $this->assertNotNull($result['attribute_group_id']);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/empty_attribute_group.php
     */
    public function testDeleteGroup()
    {
        $group = $this->getGroupByName('empty_attribute_group');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . "/groups/" . $group->getId(),
                'httpMethod' => \Magento\Webapi\Model\Rest\Config::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteById',
            ],
        ];
        $this->assertTrue($this->_webApiCall($serviceInfo, ['groupId' => $group->getId()]));
    }

    /**
     * @expectedException \Exception
     */
    public function testCreateGroupWithAttributeSetThatDoesNotExist()
    {
        $attributeSetId = -1;
        $this->createGroup($attributeSetId);
    }

    /**
     * @magentoApiDataFixture Magento/Catalog/_files/empty_attribute_group.php
     */
    public function testUpdateGroup()
    {
        $attributeSetId = 1;
        $group = $this->getGroupByName('empty_attribute_group');

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $attributeSetId . '/groups',
                'httpMethod' => RestConfig::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        $newGroupData = $this->createGroupData($attributeSetId);
        $newGroupData['attribute_group_name'] = 'empty_attribute_group_updated';
        $newGroupData['attribute_group_id'] = $group->getId();

        $result = $this->_webApiCall($serviceInfo, ['group' => $newGroupData]);

        $this->assertArrayHasKey('attribute_group_id', $result);
        $this->assertEquals($group->getId(), $result['attribute_group_id']);
        $this->assertArrayHasKey('attribute_group_name', $result);
        $this->assertEquals($newGroupData['attribute_group_name'], $result['attribute_group_name']);
    }

    public function testGetList()
    {
        $searchCriteria = [
            'searchCriteria' => [
                'filter_groups' => [
                    [
                        'filters' => [
                            [
                                'field' => 'attribute_set_id',
                                'value' => 1,
                                'condition_type' => 'eq',
                            ],
                        ],
                    ],
                ],
                'current_page' => 1,
                'page_size' => 2,
            ],
        ];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/groups/list' . '?' . http_build_query($searchCriteria),
                'httpMethod' => \Magento\Webapi\Model\Rest\Config::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'GetList',
            ],
        ];

        $response = $this->_webApiCall($serviceInfo, $searchCriteria);

        $this->assertArrayHasKey('search_criteria', $response);
        $this->assertArrayHasKey('total_count', $response);
        $this->assertArrayHasKey('items', $response);

        $this->assertEquals($searchCriteria['searchCriteria'], $response['search_criteria']);
        $this->assertTrue($response['total_count'] > 0);
        $this->assertTrue(count($response['items']) > 0);

        $this->assertNotNull($response['items'][0]['attribute_group_name']);
        $this->assertNotNull($response['items'][0]['attribute_group_id']);
    }

    /**
     * @param $attributeSetId
     * @return array|bool|float|int|string
     */
    protected function createGroup($attributeSetId, $groupData = null)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/groups',
                'httpMethod' => RestConfig::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        return $this->_webApiCall(
            $serviceInfo,
            ['group' => $groupData ? $groupData : $this->createGroupData($attributeSetId)]
        );
    }

    /**
     * @param $attributeSetId
     * @return array
     */
    protected function createGroupData($attributeSetId)
    {
        return [
            'attribute_group_name' => 'empty_attribute_group',
            'attribute_set_id' => $attributeSetId
        ];
    }

    /**
     * Retrieve attribute group based on given name.
     * This utility methods assumes that there is only one attribute group with given name,
     *
     * @param string $groupName
     * @return \Magento\Eav\Model\Entity\Attribute\Group|null
     */
    protected function getGroupByName($groupName)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Eav\Model\Entity\Attribute\Group */
        $attributeGroup = $objectManager->create('Magento\Eav\Model\Entity\Attribute\Group')
            ->load($groupName, 'attribute_group_name');
        if ($attributeGroup->getId() === null) {
            return null;
        }
        return $attributeGroup;
    }
}
