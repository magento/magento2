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
namespace Magento\Framework\View\Element;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Template
     */
    protected $_block;

    /**
     * @var \Magento\Framework\App\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\View\TemplateEngineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_templateEngine;

    /**
     * @var \Magento\Framework\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_viewFileSystem;

    protected function setUp()
    {
        $this->_viewFileSystem = $this->getMock('\Magento\Framework\View\FileSystem', array(), array(), '', false);

        $this->_filesystem = $this->getMock('\Magento\Framework\App\Filesystem', array(), array(), '', false);

        $this->_templateEngine = $this->getMock(
            'Magento\Framework\View\TemplateEnginePool',
            array('render', 'get'),
            array(),
            '',
            false
        );

        $this->_templateEngine->expects($this->any())->method('get')->will($this->returnValue($this->_templateEngine));

        $appState = $this->getMock('Magento\Framework\App\State', array('getAreaCode'), array(), '', false);
        $appState->expects($this->any())->method('getAreaCode')->will($this->returnValue('frontend'));
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_block = $helper->getObject(
            'Magento\Framework\View\Element\Template',
            array(
                'filesystem' => $this->_filesystem,
                'enginePool' => $this->_templateEngine,
                'viewFileSystem' => $this->_viewFileSystem,
                'appState' => $appState,
                'data' => array('template' => 'template.phtml', 'module_name' => 'Fixture_Module')
            )
        );
    }

    public function testGetTemplateFile()
    {
        $params = array('module' => 'Fixture_Module', 'area' => 'frontend');
        $this->_viewFileSystem->expects($this->once())->method('getTemplateFileName')->with('template.phtml', $params);
        $this->_block->getTemplateFile();
    }

    public function testFetchView()
    {
        $this->expectOutputString('');
        $directoryMock = $this->getMock('\Magento\Framework\Filesystem\Directory\Read', array(), array(), '', false);
        $directoryMock->expects($this->any())->method('getRelativePath')->will($this->returnArgument(0));
        $this->_filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryRead'
        )->will(
            $this->returnValue($directoryMock)
        );
        $this->_filesystem->expects($this->any())->method('getPath')->will($this->returnValue('themedir'));
        $directoryMock->expects(
            $this->once()
        )->method(
            'isFile'
        )->with(
            'themedir/template.phtml'
        )->will(
            $this->returnValue(true)
        );

        $output = '<h1>Template Contents</h1>';
        $vars = array('var1' => 'value1', 'var2' => 'value2');
        $this->_templateEngine->expects($this->once())->method('render')->will($this->returnValue($output));
        $this->_block->assign($vars);
        $this->assertEquals($output, $this->_block->fetchView('themedir/template.phtml'));
    }

    public function testSetTemplateContext()
    {
        $directoryMock = $this->getMock('\Magento\Framework\Filesystem\Directory\Read', array(), array(), '', false);
        $directoryMock->expects($this->any())->method('getRelativePath')->will($this->returnArgument(0));
        $this->_filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryRead'
        )->will(
            $this->returnValue($directoryMock)
        );
        $this->_filesystem->expects($this->any())->method('getPath')->will($this->returnValue('themedir'));
        $directoryMock->expects(
            $this->once()
        )->method(
            'isFile'
        )->with(
            'themedir/template.phtml'
        )->will(
            $this->returnValue(true)
        );

        $context = new \Magento\Framework\Object();
        $this->_templateEngine->expects($this->once())->method('render')->with($context);
        $this->_block->setTemplateContext($context);
        $this->_block->fetchView('themedir/template.phtml');
    }
}
