<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element;

use Magento\Framework\Filesystem\DriverPool;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Template
     */
    protected $_block;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\View\TemplateEngineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_templateEngine;

    /**
     * @var \Magento\Framework\View\Element\Template\File\Resolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resolver;

    /**
     * @var \Magento\Framework\View\Element\Template\File\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_validator;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rootDirMock;

    protected function setUp()
    {
        $this->_resolver = $this->getMock(
            'Magento\Framework\View\Element\Template\File\Resolver',
            [],
            [],
            '',
            false
        );

        $this->_validator = $this->getMock(
            'Magento\Framework\View\Element\Template\File\Validator',
            [],
            [],
            '',
            false
        );

        $this->rootDirMock = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $this->rootDirMock->expects($this->any())
            ->method('getRelativePath')
            ->will($this->returnArgument(0));
        $appDirMock = $this->getMock('\Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $themesDirMock = $this->getMock('\Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $themesDirMock->expects($this->any())
            ->method('getAbsolutePath')
            ->will($this->returnValue('themedir'));

        $this->_filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $this->_filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->will($this->returnValueMap([
                [\Magento\Framework\App\Filesystem\DirectoryList::THEMES, DriverPool::FILE, $themesDirMock],
                [\Magento\Framework\App\Filesystem\DirectoryList::APP, DriverPool::FILE, $appDirMock],
                [\Magento\Framework\App\Filesystem\DirectoryList::ROOT, DriverPool::FILE, $this->rootDirMock],
                [
                    \Magento\Framework\App\Filesystem\DirectoryList::TEMPLATE_MINIFICATION_DIR, DriverPool::FILE,
                    $this->rootDirMock
                ],
            ]));

        $this->_templateEngine = $this->getMock(
            'Magento\Framework\View\TemplateEnginePool',
            ['render', 'get'],
            [],
            '',
            false
        );

        $this->_templateEngine->expects($this->any())->method('get')->will($this->returnValue($this->_templateEngine));

        $appState = $this->getMock('Magento\Framework\App\State', ['getAreaCode'], [], '', false);
        $appState->expects($this->any())->method('getAreaCode')->will($this->returnValue('frontend'));
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_block = $helper->getObject(
            'Magento\Framework\View\Element\Template',
            [
                'filesystem' => $this->_filesystem,
                'enginePool' => $this->_templateEngine,
                'resolver' => $this->_resolver,
                'validator' => $this->_validator,
                'appState' => $appState,
                'data' => ['template' => 'template.phtml', 'module_name' => 'Fixture_Module']
            ]
        );
    }

    public function testGetTemplateFile()
    {
        $params = ['module' => 'Fixture_Module', 'area' => 'frontend'];
        $this->_resolver->expects($this->once())->method('getTemplateFileName')->with('template.phtml', $params);
        $this->_block->getTemplateFile();
    }

    public function testFetchView()
    {
        $this->expectOutputString('');
        $template = 'themedir/template.phtml';
        $this->_validator->expects($this->once())
            ->method('isValid')
            ->with($template)
            ->will($this->returnValue(true));

        $output = '<h1>Template Contents</h1>';
        $vars = ['var1' => 'value1', 'var2' => 'value2'];
        $this->_templateEngine->expects($this->once())->method('render')->will($this->returnValue($output));
        $this->_block->assign($vars);
        $this->assertEquals($output, $this->_block->fetchView($template));
    }

    public function testSetTemplateContext()
    {
        $template = 'themedir/template.phtml';
        $context = new \Magento\Framework\Object();
        $this->_validator->expects($this->once())
            ->method('isValid')
            ->with($template)
            ->will($this->returnValue(true));

        $this->_templateEngine->expects($this->once())->method('render')->with($context);
        $this->_block->setTemplateContext($context);
        $this->_block->fetchView($template);
    }
}
