<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\Template
     */
    protected $block;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\View\TemplateEngineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $templateEngine;

    /**
     * @var \Magento\Framework\View\Element\Template\File\Resolver|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resolver;

    /**
     * @var \Magento\Framework\View\Element\Template\File\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validator;

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
        $this->resolver = $this->getMock(
            'Magento\Framework\View\Element\Template\File\Resolver',
            [],
            [],
            '',
            false
        );

        $this->validator = $this->getMock(
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

        $this->filesystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT, DriverPool::FILE)
            ->willReturn($this->rootDirMock);

        $this->templateEngine = $this->getMock(
            'Magento\Framework\View\TemplateEnginePool',
            ['render', 'get'],
            [],
            '',
            false
        );
        $this->loggerMock = $this->getMock('Psr\Log\LoggerInterface');
        $this->templateEngine->expects($this->any())->method('get')->willReturn($this->templateEngine);

        $appState = $this->getMock('Magento\Framework\App\State', ['getAreaCode'], [], '', false);
        $appState->expects($this->any())->method('getAreaCode')->willReturn('frontend');
        $storeManagerMock = $this->getMock(StoreManager::class, [], [], '', false);
        $storeMock = $this->getMock(Store::class, [], [], '', false);
        $storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->any())
            ->method('getCode')
            ->willReturn('storeCode');
        $urlBuilderMock = $this->getMock(UrlInterface::class, [], [], '', false);
        $urlBuilderMock->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn('baseUrl');
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $helper->getObject(
            'Magento\Framework\View\Element\Template',
            [
                'filesystem' => $this->filesystem,
                'enginePool' => $this->templateEngine,
                'resolver' => $this->resolver,
                'validator' => $this->validator,
                'appState' => $appState,
                'logger' => $this->loggerMock,
                'storeManager' => $storeManagerMock,
                'urlBuilder' => $urlBuilderMock,
                'data' => ['template' => 'template.phtml', 'module_name' => 'Fixture_Module']
            ]
        );
    }

    public function testGetTemplateFile()
    {
        $params = ['module' => 'Fixture_Module', 'area' => 'frontend'];
        $this->resolver->expects($this->once())->method('getTemplateFileName')->with('template.phtml', $params);
        $this->block->getTemplateFile();
    }

    public function testFetchView()
    {
        $this->expectOutputString('');
        $template = 'themedir/template.phtml';
        $this->validator->expects($this->once())
            ->method('isValid')
            ->with($template)
            ->willReturn(true);
        $output = '<h1>Template Contents</h1>';
        $vars = ['var1' => 'value1', 'var2' => 'value2'];
        $this->templateEngine->expects($this->once())->method('render')->willReturn($output);
        $this->block->assign($vars);
        $this->assertEquals($output, $this->block->fetchView($template));
    }

    public function testFetchViewWithNoFileName()
    {
        $output = '';
        $template = false;
        $templatePath = 'wrong_template_path.pthml';
        $moduleName = 'Acme';
        $blockName = 'acme_test_module_test_block';
        $exception = "Invalid template file: '{$templatePath}' in module: '{$moduleName}' block's name: '{$blockName}'";
        $this->block->setTemplate($templatePath);
        $this->block->setData('module_name', $moduleName);
        $this->block->setNameInLayout($blockName);
        $this->validator->expects($this->once())
            ->method('isValid')
            ->with($template)
            ->willReturn(false);
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception)
            ->willReturn(null);
        $this->assertEquals($output, $this->block->fetchView($template));
    }

    public function testSetTemplateContext()
    {
        $template = 'themedir/template.phtml';
        $context = new \Magento\Framework\DataObject();
        $this->validator->expects($this->once())
            ->method('isValid')
            ->with($template)
            ->willReturn(true);
        $this->templateEngine->expects($this->once())->method('render')->with($context);
        $this->block->setTemplateContext($context);
        $this->block->fetchView($template);
    }

    public function testGetCacheKeyInfo()
    {
        $this->assertEquals(
            [
                'BLOCK_TPL',
                'storeCode',
                null,
                'base_url' => 'baseUrl',
                'template' => 'template.phtml',
            ],
            $this->block->getCacheKeyInfo()
        );
    }
}
