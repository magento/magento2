<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Renderer;

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
