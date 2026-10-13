<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Renderer;

use Magento\Customer\Model\Renderer\Region;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class RegionTest extends TestCase
{
    use MockCreationTrait;

    /**
     * Simulate "serialize" method of a form element.
     *
     * @param string[] $keys
     * @param array $data
     * @return string
     */
    private function mockSerialize(array $keys, array $data): string
    {
        $attributes = [];
        foreach ($keys as $key) {
            if (empty($data[$key])) {
                continue;
            }
            $attributes[] = $key .'="' .$data[$key] .'"';
        }

        return implode(' ', $attributes);
    }

    /**
     * @param array $regionCollection
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     **/
    #[DataProvider('renderDataProvider')]
    public function testRender($regionCollection)
    {
        $countryFactoryMock = $this->createMock(
            CountryFactory::class
        );
        $directoryHelperMock = $this->createPartialMock(
            Data::class,
            ['isRegionRequired']
        );
        $escaperMock = $this->createMock(Escaper::class);
        /** @var MockObject|AbstractElement $elementMock */
        $elementMock = $this->createPartialMock(
            AbstractElement::class,
            ['getForm', 'getHtmlAttributes', 'serialize']
        );
        $elementMock->method('serialize')->willReturnCallback(
            function (array $attributes) use ($elementMock): string {
                return $this->mockSerialize($attributes, $elementMock->getData());
            }
        );
        $countryMock = $this->createPartialMockWithReflection(
            AbstractElement::class,
            ['serialize', 'getValue']
        );
        $countryMock->method('serialize')->willReturnCallback(
            function (array $attributes) use ($countryMock): string {
                return $this->mockSerialize($attributes, $countryMock->getData());
            }
        );
        $regionMock = $this->createMock(
            AbstractElement::class
        );
        $countryModelMock = $this->createPartialMockWithReflection(
            Country::class,
            [
                'toOptionArray',
                'setId',
                'getLoadedRegionCollection',
                '__wakeup'
            ]
        );
        $formMock = $this->createPartialMock(Form::class, ['getElement']);

        $elementMock->expects($this->any())->method('getForm')->willReturn($formMock);
        $elementMock->expects(
            $this->any()
        )->method(
            'getHtmlAttributes'
        )->willReturn(
            [
                'title',
                'class',
                'style',
                'onclick',
                'onchange',
                'disabled',
                'readonly',
                'tabindex',
                'placeholder',
            ]
        );

        $objectManager = new ObjectManager($this);
        $escaper = $objectManager->getObject(Escaper::class);
        $reflection = new \ReflectionClass($elementMock);
        $reflection_property = $reflection->getProperty('_escaper');
        $reflection_property->setValue($elementMock, $escaper);

        $formMock->expects(
            $this->any()
        )->method(
            'getElement'
        )->willReturnMap(
            [['country_id', $countryMock], ['region_id', $regionMock]]
        );
        $countryMock->expects($this->any())->method('getValue')->willReturn('GE');
        $directoryHelperMock->expects(
            $this->any()
        )->method(
            'isRegionRequired'
        )->willReturnMap(
            [['GE', true]]
        );
        $countryFactoryMock->expects($this->once())->method('create')->willReturn($countryModelMock);
        $countryModelMock->expects($this->any())->method('setId')->willReturnSelf();
        $countryModelMock->expects($this->any())->method('getLoadedRegionCollection')->willReturnSelf();
        $countryModelMock->expects($this->any())->method('toOptionArray')->willReturn($regionCollection);

        $model = new Region($countryFactoryMock, $directoryHelperMock, $escaperMock);

        $static = new \ReflectionProperty(Region::class, '_regionCollections');
        $static->setValue(null, null);

        $html = $model->render($elementMock);

        $this->assertStringContainsString('required', $html);
        $this->assertStringContainsString('required-entry', $html);
    }

    /**
     * @return array
     */
    public static function renderDataProvider()
    {
        return [
            'with no defined regions' => [[]],
            'with defined regions' => [
                [
                    new DataObject(['value' => 'Bavaria']),
                    new DataObject(['value' => 'Saxony']),
                ],
            ]
        ];
    }
}
