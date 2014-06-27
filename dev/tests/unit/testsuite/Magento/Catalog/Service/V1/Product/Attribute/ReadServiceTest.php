<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Service\V1\Product\Attribute;

use Magento\Catalog\Service\V1\ProductMetadataServiceInterface;
use Magento\Framework\Service\V1\Data\SearchCriteriaBuilder;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for retrieving product attributes types
     */
    public function testTypes()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $inputTypeFactoryMock = $this->getMock(
            'Magento\Catalog\Model\Product\Attribute\Source\InputtypeFactory',
            array('create')
        );
        $inputTypeFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue(
                $objectManager->getObject('Magento\Catalog\Model\Product\Attribute\Source\Inputtype')
            ));

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $attributeTypeBuilder = $helper->getObject(
            '\Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\TypeBuilder'
        );
        $productAttributeReadService = $objectManager->getObject(
            '\Magento\Catalog\Service\V1\Product\Attribute\ReadService',
            [
                'metadataService' => $objectManager->getObject('Magento\Catalog\Service\V1\ProductMetadataService'),
                'inputTypeFactory' => $inputTypeFactoryMock,
                'attributeTypeBuilder' => $attributeTypeBuilder
            ]
        );
        $types = $productAttributeReadService->types();
        $this->assertTrue(is_array($types));
        $this->assertNotEmpty($types);
        $this->assertInstanceOf('Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\Type', current($types));
    }

    /**
     * Test for retrieving product attribute
     */
    public function testInfo()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $attributeCode = 'attr_code';
        $metadataServiceMock = $this->getMock(
            'Magento\Catalog\Service\V1\ProductMetadataService', array('getAttributeMetadata'),
            array(),
            '',
            false
        );
        $metadataServiceMock->expects($this->once())
            ->method('getAttributeMetadata')
            ->with(
                ProductMetadataServiceInterface::ENTITY_TYPE_PRODUCT,
                $attributeCode
            );

        /** @var \Magento\Catalog\Service\V1\Product\Attribute\ReadServiceInterface $service */
        $service = $objectManager->getObject(
            'Magento\Catalog\Service\V1\Product\Attribute\ReadService',
            array(
               'metadataService' => $metadataServiceMock
            )
        );
        $service->info($attributeCode);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSearch()
    {
        $attrCollectionMock = $this->getMock('Magento\Eav\Model\Resource\Entity\Attribute\Collection',
            array(), array(), '', false);
        $attrCollectionMock->expects($this->any())->method('addOrder')->with(
            $this->equalTo('attribute_id'),
            $this->equalTo('DESC')
        );
        $attrCollectionMock->expects($this->once())->method('getTable')->with(
            $this->equalTo('catalog_eav_attribute')
        )->will(
            $this->returnValue('catalog_eav_attribute')
        );
        $attrCollectionMock->expects($this->once())->method('join')->with(
            $this->equalTo(array('additional_table' => 'catalog_eav_attribute')),
            $this->equalTo('main_table.attribute_id = additional_table.attribute_id'),
            $this->equalTo(
                [
                    'frontend_input_renderer',
                    'is_global',
                    'is_visible',
                    'is_searchable',
                    'is_filterable',
                    'is_comparable',
                    'is_visible_on_front',
                    'is_html_allowed_on_front',
                    'is_used_for_price_rules',
                    'is_filterable_in_search',
                    'used_in_product_listing',
                    'used_for_sort_by',
                    'apply_to',
                    'is_visible_in_advanced_search',
                    'position',
                    'is_wysiwyg_enabled',
                    'is_used_for_promo_rules',
                    'is_configurable',
                    'search_weight',
                ]
            )
        );
        $attrCollectionMock->expects($this->once())->method('getSize')->will($this->returnValue(1));
        $attrCollectionMock->expects($this->once())->method('addFieldToFilter')->with(
            $this->equalTo(array('frontend_input')),
            $this->equalTo(array(array('eq' => 'text')))
        );

        $attributeData = array(
            'attribute_id' => 1,
            'attribute_code' => 'status',
            'frontend_label' => 'Status',
            'default_value' => '1',
            'is_required' => false,
            'is_user_defined' => false,
            'frontend_input' => 'text'
        );
        // Use magento object for simplicity
        $attributeElement = new \Magento\Framework\Object($attributeData);
        $this->_mockReturnValue(
            $attrCollectionMock,
            array(
                'getSize' => 1,
                '_getItems' => array($attributeElement),
                'getIterator' => new \ArrayIterator(array($attributeElement))
            )
        );

        $attributeBuilderMock = $this->getMock('\Magento\Catalog\Service\V1\Data\Eav\AttributeBuilder',
            array(), array(), '', false);

        $attributeBuilderMock->expects($this->once())
            ->method('setId')
            ->with($attributeData['attribute_id'])
            ->will($this->returnSelf());
        $attributeBuilderMock->expects($this->once())
            ->method('setCode')
            ->with($attributeData['attribute_code'])
            ->will($this->returnSelf());
        $attributeBuilderMock->expects($this->once())
            ->method('setFrontendLabel')
            ->with($attributeData['frontend_label'])
            ->will($this->returnSelf());
        $attributeBuilderMock->expects($this->once())
            ->method('setDefaultValue')
            ->with($attributeData['default_value'])
            ->will($this->returnSelf());
        $attributeBuilderMock->expects($this->once())
            ->method('setIsRequired')
            ->with($attributeData['is_required'])
            ->will($this->returnSelf());
        $attributeBuilderMock->expects($this->once())
            ->method('setIsUserDefined')
            ->with($attributeData['is_user_defined'])
            ->will($this->returnSelf());
        $attributeBuilderMock->expects($this->once())
            ->method('setFrontendInput')
            ->with($attributeData['frontend_input'])
            ->will($this->returnSelf());

        $dataObjectMock = $this->getMock(
            '\Magento\Catalog\Service\V1\Data\Eav\Attribute',
            array(),
            array(),
            '',
            false
        );
        $attributeBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($dataObjectMock));

        $searchResultsBuilderMock = $this->getMockBuilder(
                'Magento\Catalog\Service\V1\Data\Product\Attribute\SearchResultsBuilder'
            )->disableOriginalConstructor()
            ->getMock();

        $searchResultsBuilderMock->expects($this->once())
            ->method('setItems')
            ->with($this->equalTo(array($dataObjectMock)));
        $searchResultsBuilderMock->expects($this->once())
            ->method('create');

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $filterBuilder = $helper->getObject('Magento\Framework\Service\V1\Data\FilterBuilder');
        $filter = $filterBuilder->setField('frontend_input')->setValue('text')->setConditionType('eq')->create();

        $filterGroupBuilder = $helper->getObject('Magento\Framework\Service\V1\Data\Search\FilterGroupBuilder');
        /** @var SearchCriteriaBuilder $searchBuilder */
        $searchBuilder = $helper->getObject(
            'Magento\Framework\Service\V1\Data\SearchCriteriaBuilder',
            ['filterGroupBuilder' => $filterGroupBuilder]
        );
        $searchBuilder->addFilter([$filter]);
        $searchBuilder->addSortOrder('id', \Magento\Framework\Service\V1\Data\SearchCriteria::SORT_DESC);
        $searchBuilder->setCurrentPage(1);
        $searchBuilder->setPageSize(10);

        $prductAttrService = $helper->getObject(
            'Magento\Catalog\Service\V1\Product\Attribute\ReadService',
            [
                'searchResultsBuilder' => $searchResultsBuilderMock,
                'attributeCollection' => $attrCollectionMock,
                'attributeBuilder' => $attributeBuilderMock
            ]
        );
        $prductAttrService->search($searchBuilder->create());
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $valueMap
     */
    private function _mockReturnValue($mock, $valueMap)
    {
        foreach ($valueMap as $method => $value) {
            $mock->expects($this->any())->method($method)->will($this->returnValue($value));
        }
    }
}
