<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element;

use Magento\Framework\App\Filesystem\DirectoryList;
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

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

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
            ->willReturnArgument(0);

        $this->_filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $this->_filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT, DriverPool::FILE)
            ->willReturn($this->rootDirMock);

        $this->_templateEngine = $this->getMock(
            'Magento\Framework\View\TemplateEnginePool',
            ['render', 'get'],
            [],
            '',
            false
        );
        $this->loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $this->_templateEngine->expects($this->any())->method('get')->willReturn($this->_templateEngine);

        $appState = $this->getMock('Magento\Framework\App\State', ['getAreaCode'], [], '', false);
        $appState->expects($this->any())->method('getAreaCode')->willReturn('frontend');
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_block = $helper->getObject(
            'Magento\Framework\View\Element\Template',
            [
                'filesystem' => $this->_filesystem,
                'enginePool' => $this->_templateEngine,
                'resolver' => $this->_resolver,
                'validator' => $this->_validator,
                'appState' => $appState,
                'logger' => $this->loggerMock,
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
            ->willReturn(true);
        $output = '<h1>Template Contents</h1>';
        $vars = ['var1' => 'value1', 'var2' => 'value2'];
        $this->_templateEngine->expects($this->once())->method('render')->willReturn($output);
        $this->_block->assign($vars);
        $this->assertEquals($output, $this->_block->fetchView($template));
    }

    public function testFetchViewWithNoFileName()
    {
        $output = '';
        $template = false;
        $templateFile = 'wrong_template_path.pthml';
        $moduleName = 'Acme';
        $blockName = 'acme_test_module_test_block';
        $exception = "Invalid template file: '{$templateFile}' in module: '{$moduleName}' block's name: '{$blockName}'";
        $this->_block->setTemplate($templateFile);
        $this->_block->setData('module_name', $moduleName);
        $this->_block->setNameInLayout($blockName);
        $this->_validator->expects($this->once())
            ->method('isValid')
            ->with($template)
            ->willReturn(false);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception)
            ->willReturn(null);
        $this->assertEquals($output, $this->_block->fetchView($template));
    }

    public function testSetTemplateContext()
    {
        $template = 'themedir/template.phtml';
        $context = new \Magento\Framework\DataObject();
        $this->_validator->expects($this->once())
            ->method('isValid')
            ->with($template)
            ->willReturn(true);
        $this->_templateEngine->expects($this->once())->method('render')->with($context);
        $this->_block->setTemplateContext($context);
        $this->_block->fetchView($template);
    }
}
