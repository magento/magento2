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
use PHPUnit\Framework\MockObject\MockObject;

class DefaultFrontendTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DefaultFrontend
     */
    private $model;

    /**
     * @var BooleanFactory|MockObject
     */
    private $booleanFactory;

    /**
     * @var Serializer|MockObject
     */
    private $serializerMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var AbstractAttribute|MockObject
     */
    private $attributeMock;

    /**
     * @var array
     */
    private $cacheTags;

    /**
     * @var AbstractSource|MockObject
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
        $this->attributeMock = $this->createAttributeMock();
        $this->sourceMock = $this->getMockBuilder(AbstractSource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllOptions'])
            ->getMockForAbstractClass();

        $this->model = new DefaultFrontend(
            $this->booleanFactory,
            $this->cacheMock,
            null,
            $this->cacheTags,
            $this->storeManagerMock,
            $this->serializerMock
        );

        $this->model->setAttribute($this->attributeMock);
    }

    public function testGetClassEmpty()
    {
        /** @var AbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->createAttributeMock();
        $attributeMock->method('getIsRequired')
            ->willReturn(false);
        $attributeMock->method('getFrontendClass')
            ->willReturn('');
        $attributeMock->expects($this->exactly(2))
            ->method('getValidateRules')
            ->willReturn('');

        $this->model->setAttribute($attributeMock);
        $this->assertEmpty($this->model->getClass());
    }

    /**
     * Validates generated html classes.
     *
     * @param string $validationRule
     * @param string $expectedClass
     * @return void
     * @dataProvider validationRulesDataProvider
     */
    public function testGetClass(string $validationRule, string $expectedClass)
    {
        /** @var AbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->createAttributeMock();
        $attributeMock->method('getIsRequired')
            ->willReturn(true);
        $attributeMock->method('getFrontendClass')
            ->willReturn('');
        $attributeMock->expects($this->exactly(3))
            ->method('getValidateRules')
            ->willReturn([
                'input_validation' => $validationRule,
                'min_text_length' => 1,
                'max_text_length' => 2,
            ]);

        $this->model->setAttribute($attributeMock);
        $result = $this->model->getClass();

        $this->assertContains($expectedClass, $result);
        $this->assertContains('minimum-length-1', $result);
        $this->assertContains('maximum-length-2', $result);
        $this->assertContains('validate-length', $result);
    }

    public function testGetClassLength()
    {
        /** @var AbstractAttribute|MockObject $attributeMock */
        $attributeMock = $this->createAttributeMock();
        $attributeMock->method('getIsRequired')
            ->willReturn(true);
        $attributeMock->method('getFrontendClass')
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

        $this->storeManagerMock->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->method('getId')
            ->willReturn($storeId);
        $this->attributeMock->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->cacheMock->method('load')
            ->with($cacheKey)
            ->willReturn(false);
        $this->attributeMock->method('getSource')
            ->willReturn($this->sourceMock);
        $this->sourceMock->method('getAllOptions')
            ->willReturn($options);
        $this->serializerMock->method('serialize')
            ->with($options)
            ->willReturn($serializedOptions);
        $this->cacheMock->method('save')
            ->with($serializedOptions, $cacheKey, $this->cacheTags);

        $this->assertSame($options, $this->model->getSelectOptions());
    }

    /**
     * Provides possible validation types.
     *
     * @return array
     */
    public function validationRulesDataProvider(): array
    {
        return [
            ['alphanumeric', 'validate-alphanum'],
            ['alphanum-with-spaces', 'validate-alphanum-with-spaces'],
            ['alpha', 'validate-alpha'],
            ['numeric', 'validate-digits'],
            ['url', 'validate-url'],
            ['email', 'validate-email'],
            ['length', 'validate-length'],
        ];
    }

    /**
     * Entity attribute factory.
     *
     * @return AbstractAttribute|MockObject
     */
    private function createAttributeMock()
    {
        return $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getIsRequired',
                'getFrontendClass',
                'getValidateRules',
                'getAttributeCode',
                'getSource'
            ])
            ->getMockForAbstractClass();
    }
}
