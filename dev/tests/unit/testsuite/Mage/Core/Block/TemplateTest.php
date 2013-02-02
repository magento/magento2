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
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Block_TemplateTest extends PHPUnit_Framework_TestCase
{
    public function testGetTemplateFile()
    {
        $design = $this->getMock('Mage_Core_Model_Design_Package', array('getFilename'), array(), '', false);
        $template = 'fixture';
        $area = 'areaFixture';
        $block = new Mage_Core_Block_Template(
            $this->getMock('Mage_Core_Controller_Request_Http'),
            $this->getMock('Mage_Core_Model_Layout', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Event_Manager'),
            $this->getMock('Mage_Core_Model_Url'),
            $this->getMock('Mage_Core_Model_Translate', array(), array($design)),
            $this->getMock('Mage_Core_Model_Cache', array(), array(), '', false),
            $design,
            $this->getMock('Mage_Core_Model_Session'),
            $this->getMock('Mage_Core_Model_Store_Config'),
            $this->getMock('Mage_Core_Controller_Varien_Front', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Factory_Helper'),
            $this->getMock('Mage_Core_Model_Dir', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Logger', array(), array(), '', false),
            $this->getMock('Magento_Filesystem', array(), array(), '', false),
            array('template' => $template, 'area' => $area)
        );

        $params = array('module' => 'Mage_Core', 'area' => $area);
        $design->expects($this->once())->method('getFilename')->with($template, $params);
        $block->getTemplateFile();
    }

    /**
     * @param string $filename
     * @param string $expectedOutput
     * @dataProvider fetchViewDataProvider
     */
    public function testFetchView($filename, $expectedOutput)
    {
        $layout = $this->getMock('Mage_Core_Model_Layout', array('isDirectOutput'), array(), '', false);
        $filesystem = new Magento_Filesystem(new Magento_Filesystem_Adapter_Local);
        $design = $this->getMock('Mage_Core_Model_Design_Package', array(), array($filesystem));
        $block = $this->getMock('Mage_Core_Block_Template', array('getShowTemplateHints'), array(
            $this->getMock('Mage_Core_Controller_Request_Http'),
            $layout,
            $this->getMock('Mage_Core_Model_Event_Manager'),
            $this->getMock('Mage_Core_Model_Url'),
            $this->getMock('Mage_Core_Model_Translate', array(), array($design)),
            $this->getMock('Mage_Core_Model_Cache', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Design_Package', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Session'),
            $this->getMock('Mage_Core_Model_Store_Config'),
            $this->getMock('Mage_Core_Controller_Varien_Front', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Factory_Helper'),
            new Mage_Core_Model_Dir(
                __DIR__ . '/_files',
                array(Mage_Core_Model_Dir::APP => ''),
                array(Mage_Core_Model_Dir::APP => __DIR__)
            ),
            $this->getMock('Mage_Core_Model_Logger', array('log'), array(), '', false),
            $filesystem
        ));
        $layout->expects($this->once())->method('isDirectOutput')->will($this->returnValue(false));

        $this->assertSame($block, $block->assign(array('varOne' => 'value1', 'varTwo' => 'value2')));
        $this->assertEquals($expectedOutput, $block->fetchView(__DIR__ . "/_files/{$filename}"));
    }

    /**
     * @return array
     */
    public function fetchViewDataProvider()
    {
        return array(
            array('template_test_assign.phtml', 'value1, value2'),
            array('invalid_file', ''),
        );
    }
}
