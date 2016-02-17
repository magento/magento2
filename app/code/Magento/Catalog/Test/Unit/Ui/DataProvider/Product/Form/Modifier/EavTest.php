<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav;
use Magento\Eav\Model\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\EavValidationRules;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection as GroupCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Eav\Model\Entity\Attribute\Group;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Eav\Model\Entity\Type as EntityType;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as AttributeCollection;
use Magento\Ui\DataProvider\Mapper\FormElement as FormElementMapper;
use Magento\Ui\DataProvider\Mapper\MetaProperties as MetaPropertiesMapper;

/**
 * Class EavTest
 *
 * @method Eav getModel
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EavTest extends AbstractModifierTest
{
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfigMock;

    /**
     * @var EavValidationRules|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavValidationRulesMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var GroupCollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupCollectionFactoryMock;

    /**
     * @var GroupCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupCollectionMock;

    /**
     * @var Group|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupMock;

    /**
     * @var EavAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;

    /**
     * @var EntityType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityTypeMock;

    /**
     * @var AttributeCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeCollectionMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var FormElementMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formElementMapperMock;

    /**
     * @var MetaPropertiesMapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metaPropertiesMapperMock;

    protected function setUp()
    {
        parent::setUp();
        $this->eavConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eavValidationRulesMock = $this->getMockBuilder(EavValidationRules::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->groupCollectionFactoryMock = $this->getMockBuilder(GroupCollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->groupCollectionMock =
            $this->getMockBuilder(GroupCollection::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->attributeMock = $this->getMockBuilder(EavAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupMock = $this->getMockBuilder(Group::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeGroupCode'])
            ->getMock();
        $this->entityTypeMock = $this->getMockBuilder(EntityType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeCollectionMock = $this->getMockBuilder(AttributeCollection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->formElementMapperMock = $this->getMockBuilder(FormElementMapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metaPropertiesMapperMock = $this->getMockBuilder(MetaPropertiesMapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->groupCollectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->groupCollectionMock);
        $this->groupCollectionMock->expects($this->any())
            ->method('setAttributeSetFilter')
            ->willReturnSelf();
        $this->groupCollectionMock->expects($this->any())
            ->method('setSortOrder')
            ->willReturnSelf();
        $this->groupCollectionMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->groupCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
                $this->groupMock,
            ]));
        $this->attributeCollectionMock->expects($this->any())
            ->method('addFieldToSelect')
            ->willReturnSelf();
        $this->attributeCollectionMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->eavConfigMock->expects($this->any())
            ->method('getEntityType')
            ->willReturn($this->entityTypeMock);
        $this->entityTypeMock->expects($this->any())
            ->method('getAttributeCollection')
            ->willReturn($this->attributeCollectionMock);
        $this->productMock->expects($this->any())
            ->method('getAttributes')
            ->willReturn([
                $this->attributeMock,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(Eav::class, [
            'locator' => $this->locatorMock,
            'eavValidationRules' => $this->eavValidationRulesMock,
            'eavConfig' => $this->eavConfigMock,
            'request' => $this->requestMock,
            'groupCollectionFactory' => $this->groupCollectionFactoryMock,
            'storeManager' => $this->storeManagerMock,
            'formElementMapper' => $this->formElementMapperMock,
            'metaPropertiesMapper' => $this->metaPropertiesMapperMock,
        ]);
    }

    public function testModifyData()
    {

    }
}
