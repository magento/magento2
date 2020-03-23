<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;

class RegionTest extends \PHPUnit\Framework\TestCase
{
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
     * @dataProvider renderDataProvider
     */
    public function testRender($regionCollection)
    {
        $countryFactoryMock = $this->createMock(
            \Magento\Directory\Model\CountryFactory::class
        );
        $directoryHelperMock = $this->createPartialMock(
            \Magento\Directory\Helper\Data::class,
            ['isRegionRequired']
        );
        $escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        /** @var MockObject|AbstractElement $elementMock */
        $elementMock = $this->createPartialMock(
            \Magento\Framework\Data\Form\Element\AbstractElement::class,
            ['getForm', 'getHtmlAttributes', 'serialize']
        );
        $elementMock->method('serialize')->willReturnCallback(
            function (array $attributes) use ($elementMock): string {
                return $this->mockSerialize($attributes, $elementMock->getData());
            }
        );
        $countryMock = $this->createPartialMock(
            \Magento\Framework\Data\Form\Element\AbstractElement::class,
            ['getValue', 'serialize']
        );
        $countryMock->method('serialize')->willReturnCallback(
            function (array $attributes) use ($countryMock): string {
                return $this->mockSerialize($attributes, $countryMock->getData());
            }
        );
        $regionMock = $this->createMock(
            \Magento\Framework\Data\Form\Element\AbstractElement::class
        );
        $countryModelMock = $this->createPartialMock(
            \Magento\Directory\Model\Country::class,
            ['setId', 'getLoadedRegionCollection', 'toOptionArray', '__wakeup']
        );
        $formMock = $this->createPartialMock(\Magento\Framework\Data\Form::class, ['getElement']);

        $elementMock->expects($this->any())->method('getForm')->will($this->returnValue($formMock));
        $elementMock->expects(
            $this->any()
        )->method(
            'getHtmlAttributes'
        )->will(
            $this->returnValue(
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
            )
        );

        $objectManager = new ObjectManager($this);
        $escaper = $objectManager->getObject(\Magento\Framework\Escaper::class);
        $reflection = new \ReflectionClass($elementMock);
        $reflection_property = $reflection->getProperty('_escaper');
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($elementMock, $escaper);

        $formMock->expects(
            $this->any()
        )->method(
            'getElement'
        )->will(
            $this->returnValueMap([['country_id', $countryMock], ['region_id', $regionMock]])
        );
        $countryMock->expects($this->any())->method('getValue')->will($this->returnValue('GE'));
        $directoryHelperMock->expects(
            $this->any()
        )->method(
            'isRegionRequired'
        )->will(
            $this->returnValueMap([['GE', true]])
        );
        $countryFactoryMock->expects($this->once())->method('create')->will($this->returnValue($countryModelMock));
        $countryModelMock->expects($this->any())->method('setId')->will($this->returnSelf());
        $countryModelMock->expects($this->any())->method('getLoadedRegionCollection')->will($this->returnSelf());
        $countryModelMock->expects($this->any())->method('toOptionArray')->will($this->returnValue($regionCollection));

        $model = new \Magento\Customer\Model\Renderer\Region($countryFactoryMock, $directoryHelperMock, $escaperMock);

        $static = new \ReflectionProperty(\Magento\Customer\Model\Renderer\Region::class, '_regionCollections');
        $static->setAccessible(true);
        $static->setValue([]);

        $html = $model->render($elementMock);

        $this->assertContains('required', $html);
        $this->assertContains('required-entry', $html);
    }

    /**
     * @return array
     */
    public function renderDataProvider()
    {
        return [
            'with no defined regions' => [[]],
            'with defined regions' => [
                [
                    new \Magento\Framework\DataObject(['value' => 'Bavaria']),
                    new \Magento\Framework\DataObject(['value' => 'Saxony']),
                ],
            ]
        ];
    }
}
