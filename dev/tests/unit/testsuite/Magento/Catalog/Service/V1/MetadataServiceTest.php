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
namespace Magento\Catalog\Service\V1;

class MetadataServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Eav\Model\Resource\Entity\Attribute\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $attributeCollectionFactory;
    /** @var  \Magento\Eav\Model\Resource\Entity\Attribute\Collection|\PHPUnit_Framework_MockObject_MockObject */
    private $attributeCollection;
    /** @var \Magento\Catalog\Service\V1\Data\Eav\AttributeMetadataBuilder */
    private $attributeMetadataBuilder;
    /** @var \Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\FrontendLabelBuilder */
    private $frontendLabelBuilder;
    /** @var \Magento\Catalog\Service\V1\Data\Eav\OptionBuilder */
    private $optionBuilder;
    /** @var \Magento\Catalog\Service\V1\Data\Eav\ValidationRuleBuilder */
    private $validationRuleBuilder;
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    private $objectManager;
    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    private $eavConfigMock;
    /** @var \Magento\Catalog\Service\V1\MetadataService */
    private $service;

    protected function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->eavConfigMock = $this->getMock('Magento\Eav\Model\Config', array('getAttribute'), array(), '', false);
        $this->validationRuleBuilder = $this->objectManager->getObject(
            '\Magento\Catalog\Service\V1\Data\Eav\ValidationRuleBuilder'
        );
        $this->optionBuilder = $this->objectManager->getObject('\Magento\Catalog\Service\V1\Data\Eav\OptionBuilder');
        $this->frontendLabelBuilder = $this->objectManager->getObject(
            '\Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\FrontendLabelBuilder'
        );
        /** @var \Magento\Catalog\Service\V1\Data\Eav\AttributeMetadataBuilder $attrMetadataBuilder */
        $this->attributeMetadataBuilder = $this->objectManager->getObject(
            'Magento\Catalog\Service\V1\Data\Eav\AttributeMetadataBuilder',
            [
                'optionBuilder' => $this->optionBuilder,
                'validationRuleBuilder' => $this->validationRuleBuilder,
                'frontendLabelBuilder' => $this->frontendLabelBuilder,
            ]
        );

        $this->attributeCollectionFactory = $this->getMockBuilder(
            'Magento\Eav\Model\Resource\Entity\Attribute\CollectionFactory'
        )
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->attributeCollection = $this->getMockBuilder('Magento\Eav\Model\Resource\Entity\Attribute\Collection')
            ->disableOriginalConstructor()
            ->setMethods(
                ['__wakeup', 'getTable', 'join', 'addFieldToFilter', 'addAttributeGrouping', 'getSize', 'getIterator']
            )
            ->getMock();

        $this->service = $this->objectManager->getObject(
            'Magento\Catalog\Service\V1\MetadataService',
            array(
                'eavConfig' => $this->eavConfigMock,
                'attributeMetadataBuilder' => $this->attributeMetadataBuilder,
                'attributeCollectionFactory' => $this->attributeCollectionFactory,
            )
        );
    }

    /**
     * Test getAttributeMetadata
     */
    public function testGetAttributeMetadata()
    {
        $data = [
            'attribute_id' => 1,
            'attribute_code' => 'description',
            'frontend_label' => 'English',
            'store_labels' => array(1 => 'France'),
            'frontend_input' => 'textarea',
        ];

        $attributeMock = $this->getMock(
            'Magento\Framework\Object',
            array('usesSource', 'getSource', 'isScopeGlobal', 'getId'),
            array('data' => $data)
        );
        $attributeMock->expects($this->once())->method('getId')->will($this->returnValue($data['attribute_id']));
        $attributeMock->expects($this->any())->method('isScopeGlobal')->will($this->returnValue(true));
        $attributeMock->expects($this->any())->method('usesSource')->will($this->returnValue(true));
        $attributeMock->expects($this->any())->method('getSource')
            ->will($this->returnValue(new \Magento\Framework\Object()));

        $this->eavConfigMock->expects($this->any())->method('getAttribute')->will($this->returnValue($attributeMock));

        $dto = $this->service->getAttributeMetadata('entity_type', 'attr_code');
        $this->assertInstanceOf('Magento\Framework\Service\Data\AbstractExtensibleObject', $dto);
        $this->assertEquals($attributeMock->getFrontendInput(), $dto->getFrontendInput());

        $this->assertEquals(0, $dto->getFrontendLabel()[0]->getStoreId());
        $this->assertEquals(1, $dto->getFrontendLabel()[1]->getStoreId());
        $this->assertEquals('English', $dto->getFrontendLabel()[0]->getLabel());
        $this->assertEquals('France', $dto->getFrontendLabel()[1]->getLabel());
    }

    /**
     * Test getAllAttributeMetadata
     */
    public function testGetAllAttributeMetadata()
    {
        $data = [
            'attribute_id' => 1,
            'attribute_code' => 'description',
            'frontend_label' => 'English',
            'store_labels' => array(1 => 'France'),
            'frontend_input' => 'textarea',
        ];

        $attributeMock = $this->getMock(
            'Magento\Framework\Object',
            array('usesSource', 'getSource', 'isScopeGlobal', 'getId'),
            array('data' => $data)
        );
        $attributeMock->expects($this->once())->method('getId')->will($this->returnValue($data['attribute_id']));
        $attributeMock->expects($this->any())->method('isScopeGlobal')->will($this->returnValue(true));
        $attributeMock->expects($this->any())->method('usesSource')->will($this->returnValue(true));
        $attributeMock->expects($this->any())->method('getSource')
            ->will($this->returnValue(new \Magento\Framework\Object()));
        $this->eavConfigMock->expects($this->any())->method('getAttribute')->will($this->returnValue($attributeMock));

        $this->attributeCollectionFactory
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->attributeCollection));

        $searchCriteria = $this->getMockBuilder('\Magento\Framework\Service\V1\Data\SearchCriteria')
            ->disableOriginalConstructor()
            ->setMethods(['getFilterGroups'])
            ->getMock();
        $searchCriteria->expects($this->once())
            ->method('getFilterGroups')
            ->will($this->returnValue(array()));
        $this->attributeCollection->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(1));
        $this->attributeCollection->expects($this->once())
            ->method('getIterator')
            ->will($this->returnValue(new \ArrayIterator(array($attributeMock))));
        /** @var \Magento\Catalog\Service\V1\Data\Product\Attribute\SearchResults $searchResult */
        $searchResult = $this->service->getAllAttributeMetadata('entity_type', $searchCriteria);
        $dto = $searchResult->getItems()[0];
        $this->assertInstanceOf('Magento\Framework\Service\Data\AbstractExtensibleObject', $dto);
        $this->assertEquals($attributeMock->getFrontendInput(), $dto->getFrontendInput());

        $this->assertEquals(0, $dto->getFrontendLabel()[0]->getStoreId());
        $this->assertEquals(1, $dto->getFrontendLabel()[1]->getStoreId());
        $this->assertEquals('English', $dto->getFrontendLabel()[0]->getLabel());
        $this->assertEquals('France', $dto->getFrontendLabel()[1]->getLabel());
    }

    /**
     * Test NoSuchEntityException getAttributeMetadata
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with attributeCode = attr_code
     */
    public function testGetAttributeMetadataNoSuchEntityException()
    {
        $data = [
            'attribute_id' => 1,
            'attribute_code' => 'description',
            'frontend_label' => 'English',
            'store_labels' => array(1 => 'France'),
            'frontend_input' => 'textarea',
        ];

        $attributeMock = $this->getMock(
            'Magento\Framework\Object',
            array('getId'),
            array('data' => $data)
        );

        $this->eavConfigMock->expects($this->once())->method('getAttribute')->will($this->returnValue($attributeMock));
        $attributeMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(null));

        $this->service->getAttributeMetadata('entity_type', 'attr_code');
    }
}
