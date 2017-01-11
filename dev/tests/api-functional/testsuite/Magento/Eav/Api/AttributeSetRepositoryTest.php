<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class AttributeSetRepositoryTest extends WebapiAbstract
{
    /**
     * @magentoApiDataFixture Magento/Eav/_files/empty_attribute_set.php
     */
    public function testGet()
    {
        $attributeSetName = 'empty_attribute_set';
        $attributeSet = $this->getAttributeSetByName($attributeSetName);
        $attributeSetId = $attributeSet->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/eav/attribute-sets/' . $attributeSetId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'eavAttributeSetRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'eavAttributeSetRepositoryV1Get',
            ],
        ];
        $arguments = [
            'attributeSetId' => $attributeSetId,
        ];
        $result = $this->_webApiCall($serviceInfo, $arguments);
        $this->assertNotNull($result);
        $this->assertEquals($attributeSet->getId(), $result['attribute_set_id']);
        $this->assertEquals($attributeSet->getAttributeSetName(), $result['attribute_set_name']);
        $this->assertEquals($attributeSet->getEntityTypeId(), $result['entity_type_id']);
        $this->assertEquals($attributeSet->getSortOrder(), $result['sort_order']);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetThrowsExceptionIfRequestedAttributeSetDoesNotExist()
    {
        $attributeSetId = 9999;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/eav/attribute-sets/' . $attributeSetId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'eavAttributeSetRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'eavAttributeSetRepositoryV1Get',
            ],
        ];
        $arguments = [
            'attributeSetId' => $attributeSetId,
        ];
        $this->_webApiCall($serviceInfo, $arguments);
    }

    /**
     * @magentoApiDataFixture Magento/Eav/_files/empty_attribute_set.php
     */
    public function testSave()
    {
        $attributeSetName = 'empty_attribute_set';
        $attributeSet = $this->getAttributeSetByName($attributeSetName);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/eav/attribute-sets/' . $attributeSet->getId(),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => 'eavAttributeSetRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'eavAttributeSetRepositoryV1Save',
            ],
        ];

        $updatedSortOrder = $attributeSet->getSortOrder() + 200;

        $arguments = [
            'attributeSet' => [
                'attribute_set_id' => $attributeSet->getId(),
                // name is the same, because it is used by fixture rollback script
                'attribute_set_name' => $attributeSet->getAttributeSetName(),
                'entity_type_id' => $attributeSet->getEntityTypeId(),
                'sort_order' => $updatedSortOrder,
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, $arguments);
        $this->assertNotNull($result);
        // Reload attribute set data
        $attributeSet = $this->getAttributeSetByName($attributeSetName);
        $this->assertEquals($attributeSet->getAttributeSetId(), $result['attribute_set_id']);
        $this->assertEquals($attributeSet->getAttributeSetName(), $result['attribute_set_name']);
        $this->assertEquals($attributeSet->getEntityTypeId(), $result['entity_type_id']);
        $this->assertEquals($updatedSortOrder, $result['sort_order']);
        $this->assertEquals($attributeSet->getSortOrder(), $result['sort_order']);
    }

    /**
     * @magentoApiDataFixture Magento/Eav/_files/empty_attribute_set.php
     */
    public function testDeleteById()
    {
        $attributeSetName = 'empty_attribute_set';
        $attributeSet = $this->getAttributeSetByName($attributeSetName);
        $attributeSetId = $attributeSet->getId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/eav/attribute-sets/' . $attributeSetId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => 'eavAttributeSetRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'eavAttributeSetRepositoryV1DeleteById',
            ],
        ];

        $arguments = [
            'attributeSetId' => $attributeSetId,
        ];
        $this->assertTrue($this->_webApiCall($serviceInfo, $arguments));
        $this->assertNull($this->getAttributeSetByName($attributeSetName));
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Default attribute set can not be deleted
     */
    public function testDeleteByIdDefaultAttributeSet()
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Eav\Model\Config */
        $eavConfig = $objectManager->create(\Magento\Eav\Model\Config::class);

        $defaultAttributeSetId = $eavConfig
            ->getEntityType(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->getDefaultAttributeSetId();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/eav/attribute-sets/' . $defaultAttributeSetId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => 'eavAttributeSetRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'eavAttributeSetRepositoryV1DeleteById',
            ],
        ];

        $arguments = [
            'attributeSetId' => $defaultAttributeSetId,
        ];
        $this->assertTrue($this->_webApiCall($serviceInfo, $arguments));
    }

    /**
     * @expectedException \Exception
     */
    public function testDeleteByIdThrowsExceptionIfRequestedAttributeSetDoesNotExist()
    {
        $attributeSetId = 9999;

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/eav/attribute-sets/' . $attributeSetId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => 'eavAttributeSetRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'eavAttributeSetRepositoryV1DeleteById',
            ],
        ];

        $arguments = [
            'attributeSetId' => $attributeSetId,
        ];
        $this->_webApiCall($serviceInfo, $arguments);
    }

    /**
     * @magentoApiDataFixture Magento/Eav/_files/attribute_set_for_search.php
     */
    public function testGetList()
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = Bootstrap::getObjectManager()
            ->create(SearchCriteriaBuilder::class);

        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = Bootstrap::getObjectManager()->create(FilterBuilder::class);

        $filter1 = $filterBuilder
            ->setField('entity_type_code')
            ->setValue('catalog_product')
            ->create();
        $filter2 = $filterBuilder
            ->setField('sort_order')
            ->setValue(200)
            ->setConditionType('gteq')
            ->create();
        $filter3 = $filterBuilder
            ->setField('sort_order')
            ->setValue(300)
            ->setConditionType('lteq')
            ->create();

        $searchCriteriaBuilder->addFilters([$filter1, $filter2]);
        $searchCriteriaBuilder->addFilters([$filter3]);

        /** @var SortOrderBuilder $sortOrderBuilder */
        $sortOrderBuilder = Bootstrap::getObjectManager()->create(SortOrderBuilder::class);

        /** @var SortOrder $sortOrder */
        $sortOrder = $sortOrderBuilder->setField('sort_order')
            ->setDirection(SortOrder::SORT_ASC)
            ->create();

        $searchCriteriaBuilder->setSortOrders([$sortOrder]);

        $searchCriteriaBuilder->setPageSize(1);
        $searchCriteriaBuilder->setCurrentPage(2);

        $searchData = $searchCriteriaBuilder->create()->__toArray();
        $requestData = ['searchCriteria' => $searchData];

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/eav/attribute-sets/list' . '?' . http_build_query($requestData),
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ],
            'soap' => [
                'service' => 'eavAttributeSetRepositoryV1',
                'serviceVersion' => 'V1',
                'operation' => 'eavAttributeSetRepositoryV1GetList',
            ],
        ];

        $searchResult = $this->_webApiCall($serviceInfo, $requestData);

        $this->assertEquals(2, $searchResult['total_count']);
        $this->assertEquals(1, count($searchResult['items']));
        $this->assertEquals(
            $searchResult['items'][0]['attribute_set_name'],
            'attribute_set_3_for_search'
        );
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
}
