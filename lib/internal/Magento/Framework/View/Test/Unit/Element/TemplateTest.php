<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\File\Resolver;
use Magento\Framework\View\Element\Template\File\Validator;
use Magento\Framework\View\TemplateEngineInterface;
use Magento\Framework\View\TemplateEnginePool;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TemplateTest extends TestCase
{
    /**
     * @var Template
     */
    protected $block;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystem;

    /**
     * @var TemplateEngineInterface|MockObject
     */
    protected $templateEngine;

    /**
     * @var Resolver|MockObject
     */
    protected $resolver;

    /**
     * @var Validator|MockObject
     */
    protected $validator;

    /**
     * @var Read|MockObject
     */
    private $rootDirMock;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var State|MockObject
     */
    protected $appState;

    protected function setUp(): void
    {
        $this->resolver = $this->createMock(Resolver::class);

        $this->validator = $this->createMock(Validator::class);

        $this->rootDirMock = $this->createMock(Read::class);
        $this->rootDirMock->expects($this->any())
            ->method('getRelativePath')
            ->willReturnArgument(0);

        $this->filesystem = $this->createMock(Filesystem::class);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT, DriverPool::FILE)
            ->willReturn($this->rootDirMock);

        $this->templateEngine = $this->getMockBuilder(TemplateEnginePool::class)
            ->addMethods(['render'])
            ->onlyMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->templateEngine->expects($this->any())->method('get')->willReturn($this->templateEngine);

        $this->appState = $this->createPartialMock(State::class, ['getAreaCode', 'getMode']);
        $this->appState->expects($this->any())->method('getAreaCode')->willReturn('frontend');
        $storeManagerMock = $this->createMock(StoreManager::class);
        $storeMock = $this->createMock(Store::class);
        $storeManagerMock->expects($this->any())
            ->method('getStore')
            ->willReturn($storeMock);
        $storeMock->expects($this->any())
            ->method('getCode')
            ->willReturn('storeCode');
        $urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $urlBuilderMock->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn('baseUrl');
        $helper = new ObjectManager($this);
        $this->block = $helper->getObject(
            Template::class,
            [
                'filesystem' => $this->filesystem,
                'enginePool' => $this->templateEngine,
                'resolver' => $this->resolver,
                'validator' => $this->validator,
                'appState' => $this->appState,
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
            ->with($exception);
        $this->assertEquals($output, $this->block->fetchView($template));
    }

    public function testFetchViewWithNoFileNameDeveloperMode()
    {
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
        $this->loggerMock->expects($this->never())
            ->method('critical');
        $this->appState->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage($exception);
        $this->block->fetchView($template);
    }

    public function testSetTemplateContext()
    {
        $template = 'themedir/template.phtml';
        $context = new DataObject();
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
