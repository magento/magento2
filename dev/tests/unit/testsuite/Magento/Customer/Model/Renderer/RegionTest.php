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
 * @category    Magento
 * @package     Magento_Customer
 * @subpackage  unit_tests
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
        $countryFactoryMock = $this->getMock('Magento\Directory\Model\CountryFactory', ['create'], [], '', false);
        $directoryHelperMock = $this->getMock('Magento\Directory\Helper\Data', ['isRegionRequired'], [], '', false);
        $escaperMock = $this->getMock('Magento\Escaper', [], [], '', false);
        $elementMock = $this->getMock(
            'Magento\Data\Form\Element\AbstractElement',
            ['getForm', 'getHtmlAttributes'],
            [],
            '',
            false
        );
        $countryMock = $this->getMock('Magento\Data\Form\Element\AbstractElement', ['getValue'], [], '', false);
        $regionMock = $this->getMock('Magento\Data\Form\Element\AbstractElement', [], [], '', false);
        $countryModelMock = $this->getMock(
            'Magento\Directory\Model\Country',
            ['setId', 'getLoadedRegionCollection', 'toOptionArray', '__wakeup'],
            [],
            '',
            false
        );
        $formMock = $this->getMock('Magento\Data\Form', ['getElement'], [], '', false);

        $elementMock->expects($this->any())
            ->method('getForm')
            ->will($this->returnValue($formMock));
        $elementMock->expects($this->any())
            ->method('getHtmlAttributes')
            ->will($this->returnValue(
                ['title', 'class', 'style', 'onclick', 'onchange', 'disabled', 'readonly', 'tabindex', 'placeholder']
        ));
        $formMock->expects($this->any())
            ->method('getElement')
            ->will($this->returnValueMap([
                ['country_id', $countryMock],
                ['region_id', $regionMock]
            ]));
        $countryMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue('GE'));
        $directoryHelperMock->expects($this->any())
            ->method('isRegionRequired')
            ->will($this->returnValueMap([
                ['GE', true]
            ]));
        $countryFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($countryModelMock));
        $countryModelMock->expects($this->any())
            ->method('setId')
            ->will($this->returnSelf());
        $countryModelMock->expects($this->any())
            ->method('getLoadedRegionCollection')
            ->will($this->returnSelf());
        $countryModelMock->expects($this->any())
            ->method('toOptionArray')
            ->will($this->returnValue($regionCollection));

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
        return array(
            'with no defined regions' => [[]],
            'with defined regions' => [
                [
                    new \Magento\Object(['value' => 'Bavaria']),
                    new \Magento\Object(['value' => 'Saxony']),
                ]
            ]
        );
    }
}
