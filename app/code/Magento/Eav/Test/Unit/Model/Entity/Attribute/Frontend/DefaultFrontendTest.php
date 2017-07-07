<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity\Attribute\Frontend;

use Magento\Eav\Model\Entity\Attribute\Frontend\DefaultFrontend;
use Magento\Eav\Model\Entity\Attribute\Source\BooleanFactory;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class DefaultFrontendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultFrontend
     */
    protected $model;

    /**
     * @var BooleanFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $booleanFactory;

    /**
     * @var Serializer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMock;

    /**
     * @var array
     */
    private $cacheTags;

    /**
     * @var AbstractSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sourceMock;

    protected function setUp()
    {
        $this->cacheTags = ['tag1', 'tag2'];

        $this->booleanFactory = $this->getMockBuilder(BooleanFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializerMock = $this->getMockBuilder(Serializer::class)
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMockForAbstractClass();
        $this->cacheMock = $this->getMockBuilder(CacheInterface::class)
            ->getMockForAbstractClass();
        $this->attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode', 'getSource'])
            ->getMockForAbstractClass();
        $this->sourceMock = $this->getMockBuilder(AbstractSource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllOptions'])
            ->getMockForAbstractClass();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            DefaultFrontend::class,
            [
                '_attrBooleanFactory' => $this->booleanFactory,
                'cache' => $this->cacheMock,
                'storeManager' => $this->storeManagerMock,
                'serializer' => $this->serializerMock,
                '_attribute' => $this->attributeMock,
                'cacheTags' => $this->cacheTags
            ]
        );
    }

    public function testGetClassEmpty()
    {
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIsRequired',
                'getFrontendClass',
                'getValidateRules',
            ])
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getIsRequired')
            ->willReturn(false);
        $attributeMock->expects($this->once())
            ->method('getFrontendClass')
            ->willReturn('');
        $attributeMock->expects($this->exactly(2))
            ->method('getValidateRules')
            ->willReturn('');

        $this->model->setAttribute($attributeMock);
        $this->assertEmpty($this->model->getClass());
    }

    public function testGetClass()
    {
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIsRequired',
                'getFrontendClass',
                'getValidateRules',
            ])
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getIsRequired')
            ->willReturn(true);
        $attributeMock->expects($this->once())
            ->method('getFrontendClass')
            ->willReturn('');
        $attributeMock->expects($this->exactly(3))
            ->method('getValidateRules')
            ->willReturn([
                'input_validation' => 'alphanumeric',
                'min_text_length' => 1,
                'max_text_length' => 2,
            ]);

        $this->model->setAttribute($attributeMock);
        $result = $this->model->getClass();

        $this->assertContains('validate-alphanum', $result);
        $this->assertContains('minimum-length-1', $result);
        $this->assertContains('maximum-length-2', $result);
        $this->assertContains('validate-length', $result);
    }

    public function testGetClassLength()
    {
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIsRequired',
                'getFrontendClass',
                'getValidateRules',
            ])
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('getIsRequired')
            ->willReturn(true);
        $attributeMock->expects($this->once())
            ->method('getFrontendClass')
            ->willReturn('');
        $attributeMock->expects($this->exactly(3))
            ->method('getValidateRules')
            ->willReturn([
                'input_validation' => 'length',
                'min_text_length' => 1,
                'max_text_length' => 2,
            ]);

        $this->model->setAttribute($attributeMock);
        $result = $this->model->getClass();

        $this->assertContains('minimum-length-1', $result);
        $this->assertContains('maximum-length-2', $result);
        $this->assertContains('validate-length', $result);
    }

    public function testGetSelectOptions()
    {
        $storeId = 1;
        $attributeCode = 'attr1';
        $cacheKey = 'attribute-navigation-option-' . $attributeCode . '-' . $storeId;
        $options = ['option1', 'option2'];
        $serializedOptions = "{['option1', 'option2']}";

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);
        $this->attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with($cacheKey)
            ->willReturn(false);
        $this->attributeMock->expects($this->once())
            ->method('getSource')
            ->willReturn($this->sourceMock);
        $this->sourceMock->expects($this->once())
            ->method('getAllOptions')
            ->willReturn($options);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($options)
            ->willReturn($serializedOptions);
        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with($serializedOptions, $cacheKey, $this->cacheTags);

        $this->assertSame($options, $this->model->getSelectOptions());
    }
}
