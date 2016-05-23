<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Renderer;

class RegionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $regionCollection
     * @dataProvider renderDataProvider
     */
    public function testRender($regionCollection)
    {
        $countryFactoryMock = $this->getMock(
            'Magento\Directory\Model\CountryFactory',
            ['create'],
            [],
            '',
            false
        );
        $directoryHelperMock = $this->getMock(
            'Magento\Directory\Helper\Data',
            ['isRegionRequired'],
            [],
            '',
            false
        );
        $escaperMock = $this->getMock('Magento\Framework\Escaper', [], [], '', false);
        $elementMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\AbstractElement',
            ['getForm', 'getHtmlAttributes'],
            [],
            '',
            false
        );
        $countryMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\AbstractElement',
            ['getValue'],
            [],
            '',
            false
        );
        $regionMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\AbstractElement',
            [],
            [],
            '',
            false
        );
        $countryModelMock = $this->getMock(
            'Magento\Directory\Model\Country',
            ['setId', 'getLoadedRegionCollection', 'toOptionArray', '__wakeup'],
            [],
            '',
            false
        );
        $formMock = $this->getMock('Magento\Framework\Data\Form', ['getElement'], [], '', false);

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

        $static = new \ReflectionProperty('Magento\Customer\Model\Renderer\Region', '_regionCollections');
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
