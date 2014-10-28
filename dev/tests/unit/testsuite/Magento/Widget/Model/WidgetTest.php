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
namespace Magento\Widget\Model;

class WidgetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Model\Config\Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storage;

    /**
     * @var \Magento\Widget\Model\Widget
     */
    protected $_model;

    public function setUp()
    {
        $this->_storage = $this->getMockBuilder(
            'Magento\Widget\Model\Config\Data'
        )->disableOriginalConstructor()->getMock();
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $objectManagerHelper->getObject('Magento\Widget\Model\Widget', array('dataStorage' => $this->_storage));
        $this->_model = $objectManagerHelper->getObject(
            'Magento\Widget\Model\Widget',
            array('dataStorage' => $this->_storage)
        );
    }

    public function testGetWidgets()
    {
        $expected = array('val1', 'val2');
        $this->_storage->expects($this->once())->method('get')->will($this->returnValue($expected));
        $result = $this->_model->getWidgets();
        $this->assertEquals($expected, $result);
    }

    public function testGetWidgetsWithFilter()
    {
        $configFile = __DIR__ . '/_files/mappedConfigArrayAll.php';
        $widgets = include $configFile;
        $this->_storage->expects($this->once())->method('get')->will($this->returnValue($widgets));
        $result = $this->_model->getWidgets(array('name' => 'CMS Page Link', 'description' => 'Link to a CMS Page'));
        $configFileOne = __DIR__ . '/_files/mappedConfigArray1.php';
        $expected = array('cms_page_link' => include $configFileOne);
        $this->assertEquals($expected, $result);
    }

    public function testGetWidgetsWithUnknownFilter()
    {
        $configFile = __DIR__ . '/_files/mappedConfigArrayAll.php';
        $widgets = include $configFile;
        $this->_storage->expects($this->once())->method('get')->will($this->returnValue($widgets));
        $result = $this->_model->getWidgets(array('name' => 'unknown', 'description' => 'unknown'));
        $expected = array();
        $this->assertEquals($expected, $result);
    }

    public function testGetWidgetByClassType()
    {
        $widgetOne = array('@' => array('type' => 'type1'));
        $widgets = array('widget1' => $widgetOne);
        $this->_storage->expects($this->any())->method('get')->will($this->returnValue($widgets));
        $this->assertEquals($widgetOne, $this->_model->getWidgetByClassType('type1'));
        $this->assertNull($this->_model->getWidgetByClassType('type2'));
    }

    public function testGetWidgetDeclarationTypeWithBackslashes()
    {
        $this->assertContains(
            'Magento\\\\Widget\\\\Backslashed\\\\ClassName',
            $this->_model->getWidgetDeclaration('Magento\Widget\Backslashed\ClassName')
        );
    }
}
