<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Renderer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class RegionTest
 * Test for Region
 */
class RegionTest extends \PHPUnit\Framework\TestCase
{
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
        $elementMock = $this->createPartialMock(
            \Magento\Framework\Data\Form\Element\AbstractElement::class,
            ['getForm', 'getHtmlAttributes']
        );
        $countryMock = $this->createPartialMock(
            \Magento\Framework\Data\Form\Element\AbstractElement::class,
            ['getValue']
        );
        $regionMock = $this->createMock(
            \Magento\Framework\Data\Form\Element\AbstractElement::class
        );
        $countryModelMock = $this->createPartialMock(
            \Magento\Directory\Model\Country::class,
            ['setId', 'getLoadedRegionCollection', 'toOptionArray', '__wakeup']
        );
        $formMock = $this->createPartialMock(\Magento\Framework\Data\Form::class, ['getElement']);

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
        $escaper = $objectManager->getObject(\Magento\Framework\Escaper::class);
        $reflection = new \ReflectionClass($elementMock);
        $reflection_property = $reflection->getProperty('_escaper');
        $reflection_property->setAccessible(true);
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

        $model = new \Magento\Customer\Model\Renderer\Region($countryFactoryMock, $directoryHelperMock, $escaperMock);

        $static = new \ReflectionProperty(\Magento\Customer\Model\Renderer\Region::class, '_regionCollections');
        $static->setAccessible(true);
        $static->setValue([]);

        $html = $model->render($elementMock);

        $this->assertStringContainsString('required', $html);
        $this->assertStringContainsString('required-entry', $html);
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
