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
        $template = 'fixture';
        $area = 'areaFixture';
        $params = array('module' => 'Mage_Core', 'area' => $area);

        $fileSystem = $this->getMock('Mage_Core_Model_View_FileSystem', array(), array(), '', false);
        $fileSystem->expects($this->once())->method('getFilename')->with($template, $params);
        $arguments = array(
            'viewFileSystem' => $fileSystem,
            'data'           => array('template' => $template, 'area' => $area),
        );
        $helper = new Magento_Test_Helper_ObjectManager($this);

        $block = $helper->getObject('Mage_Core_Block_Template', $arguments);

        $block->getTemplateFile();
    }

    /**
     * @param string $filename
     * @param string $expectedOutput
     * @dataProvider fetchViewDataProvider
     */
    public function testFetchView($filename, $expectedOutput)
    {
        $map = array(
            array(Mage_Core_Model_Dir::APP, __DIR__),
            array(Mage_Core_Model_Dir::THEMES, __DIR__ . 'design'),
        );
        $dirMock = $this->getMock('Mage_Core_Model_Dir', array(), array(), '', false, false);
        $dirMock->expects($this->any())->method('getDir')->will($this->returnValueMap($map));
        $layout = $this->getMock('Mage_Core_Model_Layout', array('isDirectOutput'), array(), '', false);
        $filesystem = new Magento_Filesystem(new Magento_Filesystem_Adapter_Local);
        $design = $this->getMock('Mage_Core_Model_View_DesignInterface', array(), array(), '', false);
        $translator = $this->getMock('Mage_Core_Model_Translate', array(), array(), '', false);

        $objectManagerMock = $this->getMock('Magento_ObjectManager', array('get', 'create', 'configure'));
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->with('Mage_Core_Model_TemplateEngine_Php')
            ->will($this->returnValue(new Mage_Core_Model_TemplateEngine_Php()));
        $engineFactory = new Mage_Core_Model_TemplateEngine_Factory($objectManagerMock);

        $arguments = array(
            'design'        => $design,
            'layout'        => $layout,
            'dirs'          => $dirMock,
            'filesystem'    => $filesystem,
            'translator'    => $translator,
            'engineFactory' => $engineFactory,
        );
        $helper = new Magento_Test_Helper_ObjectManager($this);

        $block = $this->getMock(
            'Mage_Core_Block_Template',
            array('getShowTemplateHints'),
            $helper->getConstructArguments('Mage_Core_Block_Template', $arguments)
        );
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
