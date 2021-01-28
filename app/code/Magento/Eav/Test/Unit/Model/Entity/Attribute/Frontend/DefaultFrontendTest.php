<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
     * @var BooleanFactory | MockObject
     */
    private $booleanFactory;

    /**
     * @var Serializer| MockObject
     */
    private $serializer;

    /**
     * @var StoreManagerInterface | MockObject
     */
    private $storeManager;

    /**
     * @var StoreInterface | MockObject
     */
    private $store;

    /**
     * @var CacheInterface | MockObject
     */
    private $cache;

    /**
     * @var AbstractAttribute | MockObject
     */
    private $attribute;

    /**
     * @var array
     */
    private $cacheTags;

    /**
     * @var AbstractSource | MockObject
     */
    private $source;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->cacheTags = ['tag1', 'tag2'];

        $this->booleanFactory = $this->getMockBuilder(BooleanFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(Serializer::class)
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();
        $this->store = $this->getMockBuilder(StoreInterface::class)
            ->getMockForAbstractClass();
        $this->cache = $this->getMockBuilder(CacheInterface::class)
            ->getMockForAbstractClass();
        $this->attribute = $this->createAttribute();
        $this->source = $this->getMockBuilder(AbstractSource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAllOptions'])
            ->getMockForAbstractClass();

        $this->model = new DefaultFrontend(
            $this->booleanFactory,
            $this->cache,
            null,
            $this->cacheTags,
            $this->storeManager,
            $this->serializer
        );

        $this->model->setAttribute($this->attribute);
    }

    public function testGetClassEmpty()
    {
        /** @var AbstractAttribute | MockObject $attribute */
        $attribute = $this->createAttribute();
        $attribute->method('getIsRequired')
            ->willReturn(false);
        $attribute->method('getFrontendClass')
            ->willReturn('');
        $attribute->expects($this->exactly(2))
            ->method('getValidateRules')
            ->willReturn('');

        $this->model->setAttribute($attribute);

        self::assertEmpty($this->model->getClass());
    }

    /**
     * Validates generated html classes.
     *
     * @param String $validationRule
     * @param String $expectedClass
     * @return void
     * @dataProvider validationRulesDataProvider
     */
    public function testGetClass(String $validationRule, String $expectedClass): void
    {
        /** @var AbstractAttribute | MockObject $attribute */
        $attribute = $this->createAttribute();
        $attribute->method('getIsRequired')
            ->willReturn(true);
        $attribute->method('getFrontendClass')
            ->willReturn('');
        $attribute->expects($this->exactly(3))
            ->method('getValidateRules')
            ->willReturn([
                'input_validation' => $validationRule,
                'min_text_length' => 1,
                'max_text_length' => 2,
            ]);

        $this->model->setAttribute($attribute);
        $result = $this->model->getClass();

        self::assertStringContainsString($expectedClass, $result);
        self::assertStringContainsString('minimum-length-1', $result);
        self::assertStringContainsString('maximum-length-2', $result);
        self::assertStringContainsString('validate-length', $result);
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
            ['length', 'validate-length']
        ];
    }

    public function testGetClassLength()
    {
        $attribute = $this->createAttribute();
        $attribute->method('getIsRequired')
            ->willReturn(true);
        $attribute->method('getFrontendClass')
            ->willReturn('');
        $attribute->expects($this->exactly(3))
            ->method('getValidateRules')
            ->willReturn([
                'input_validation' => 'length',
                'min_text_length' => 1,
                'max_text_length' => 2,
            ]);

        $this->model->setAttribute($attribute);
        $result = $this->model->getClass();

        self::assertStringContainsString('minimum-length-1', $result);
        self::assertStringContainsString('maximum-length-2', $result);
        self::assertStringContainsString('validate-length', $result);
    }

    /**
     * Entity attribute factory.
     *
     * @return AbstractAttribute | MockObject
     */
    private function createAttribute()
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

    public function testGetSelectOptions()
    {
        $storeId = 1;
        $attributeCode = 'attr1';
        $cacheKey = 'attribute-navigation-option-' . $attributeCode . '-' . $storeId;
        $options = ['option1', 'option2'];
        $serializedOptions = "{['option1', 'option2']}";

        $this->storeManager->method('getStore')
            ->willReturn($this->store);
        $this->store->method('getId')
            ->willReturn($storeId);
        $this->attribute->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->cache->method('load')
            ->with($cacheKey)
            ->willReturn(false);
        $this->attribute->method('getSource')
            ->willReturn($this->source);
        $this->source->method('getAllOptions')
            ->willReturn($options);
        $this->serializer->method('serialize')
            ->with($options)
            ->willReturn($serializedOptions);
        $this->cache->method('save')
            ->with($serializedOptions, $cacheKey, $this->cacheTags);

        self::assertSame($options, $this->model->getSelectOptions());
    }
}
