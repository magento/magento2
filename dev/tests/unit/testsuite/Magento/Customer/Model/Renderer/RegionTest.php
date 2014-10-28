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
namespace Magento\Customer\Model\Renderer;

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
            array('create'),
            array(),
            '',
            false
        );
        $directoryHelperMock = $this->getMock(
            'Magento\Directory\Helper\Data',
            array('isRegionRequired'),
            array(),
            '',
            false
        );
        $escaperMock = $this->getMock('Magento\Framework\Escaper', array(), array(), '', false);
        $elementMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\AbstractElement',
            array('getForm', 'getHtmlAttributes'),
            array(),
            '',
            false
        );
        $countryMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\AbstractElement',
            array('getValue'),
            array(),
            '',
            false
        );
        $regionMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\AbstractElement',
            array(),
            array(),
            '',
            false
        );
        $countryModelMock = $this->getMock(
            'Magento\Directory\Model\Country',
            array('setId', 'getLoadedRegionCollection', 'toOptionArray', '__wakeup'),
            array(),
            '',
            false
        );
        $formMock = $this->getMock('Magento\Framework\Data\Form', array('getElement'), array(), '', false);

        $elementMock->expects($this->any())->method('getForm')->will($this->returnValue($formMock));
        $elementMock->expects(
            $this->any()
        )->method(
            'getHtmlAttributes'
        )->will(
            $this->returnValue(
                array(
                    'title',
                    'class',
                    'style',
                    'onclick',
                    'onchange',
                    'disabled',
                    'readonly',
                    'tabindex',
                    'placeholder'
                )
            )
        );
        $formMock->expects(
            $this->any()
        )->method(
            'getElement'
        )->will(
            $this->returnValueMap(array(array('country_id', $countryMock), array('region_id', $regionMock)))
        );
        $countryMock->expects($this->any())->method('getValue')->will($this->returnValue('GE'));
        $directoryHelperMock->expects(
            $this->any()
        )->method(
            'isRegionRequired'
        )->will(
            $this->returnValueMap(array(array('GE', true)))
        );
        $countryFactoryMock->expects($this->once())->method('create')->will($this->returnValue($countryModelMock));
        $countryModelMock->expects($this->any())->method('setId')->will($this->returnSelf());
        $countryModelMock->expects($this->any())->method('getLoadedRegionCollection')->will($this->returnSelf());
        $countryModelMock->expects($this->any())->method('toOptionArray')->will($this->returnValue($regionCollection));

        $model = new \Magento\Customer\Model\Renderer\Region($countryFactoryMock, $directoryHelperMock, $escaperMock);

        $static = new \ReflectionProperty('Magento\Customer\Model\Renderer\Region', '_regionCollections');
        $static->setAccessible(true);
        $static->setValue(array());

        $html = $model->render($elementMock);

        $this->assertContains('required', $html);
        $this->assertContains('required-entry', $html);
    }

    public function renderDataProvider()
    {
        return array(
            'with no defined regions' => array(array()),
            'with defined regions' => array(
                array(
                    new \Magento\Framework\Object(array('value' => 'Bavaria')),
                    new \Magento\Framework\Object(array('value' => 'Saxony'))
                )
            )
        );
    }
}
