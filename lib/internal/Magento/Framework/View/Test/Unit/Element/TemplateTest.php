<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Element;

use Magento\Framework\App\Cache\StateInterface as CacheStateInterface;
use Magento\Framework\App\CacheInterface;
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TemplateTest extends TestCase
{
    use MockCreationTrait;
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

    /**
     * @var CacheInterface|MockObject
     */
    private $cache;

    /**
     * @var CacheStateInterface|MockObject
     */
    private $cacheState;

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

        $this->templateEngine = $this->createPartialMockWithReflection(
            TemplateEnginePool::class,
            ['render', 'get']
        );
        $this->loggerMock = $this->createMock(LoggerInterface::class);
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
        $storeMock->expects($this->any())->method('getId')->willReturn(1);
        $urlBuilderMock = $this->createMock(UrlInterface::class);
        $urlBuilderMock->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn('baseUrl');
        $this->cache = $this->createMock(CacheInterface::class);
        $this->cacheState = $this->createMock(CacheStateInterface::class);
        $helper = new ObjectManager($this);
        $this->block = $helper->getObject(
            Template::class,
            [
                'cache' => $this->cache,
                'cacheState' => $this->cacheState,
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
        $params = ['module' => 'Fixture_Module', 'area' => 'frontend', 'store_id' => 1];
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

    /**
     * A render that failed validation returns an empty string, which is indistinguishable from a
     * block that renders nothing by design. Caching it serves the failure for the cache lifetime.
     */
    public function testFailedRenderIsNotCached()
    {
        $this->block->setTemplate('wrong_template_path.phtml');
        $this->block->setCacheLifetime(3600);
        $this->block->setCacheKey('probe');
        $this->validator->expects($this->once())
            ->method('isValid')
            ->willReturn(false);
        $this->appState->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);
        $this->loggerMock->expects($this->once())->method('critical');
        $this->cacheState->expects($this->any())->method('isEnabled')->willReturn(true);
        $this->cache->expects($this->never())->method('save');

        $this->assertEquals('', $this->block->fetchView('wrong_template_path.phtml'));
        $this->assertFalse($this->invokeSaveCache(''));
    }

    /**
     * Positive control for the guard above: a render that succeeded is still cached.
     */
    public function testSuccessfulRenderIsCached()
    {
        $this->expectOutputString('');
        $output = '<h1>Template Contents</h1>';
        $template = 'themedir/template.phtml';
        $this->block->setCacheLifetime(3600);
        $this->block->setCacheKey('probe');
        $this->validator->expects($this->once())
            ->method('isValid')
            ->with($template)
            ->willReturn(true);
        $this->templateEngine->expects($this->once())->method('render')->willReturn($output);
        $this->cacheState->expects($this->any())->method('isEnabled')->willReturn(true);
        $this->cache->expects($this->once())
            ->method('save')
            ->with($output, Template::CUSTOM_CACHE_KEY_PREFIX . 'probe', $this->anything(), 3600);

        $this->assertEquals($output, $this->block->fetchView($template));
        $this->assertSame($this->block, $this->invokeSaveCache($output));
    }

    /**
     * A block that renders nothing by design keeps its existing behavior and is still cached, so it
     * is not re-rendered on every request. This is what separates the guard from refusing to cache
     * empty output in general.
     */
    public function testRenderThatIsEmptyByDesignIsStillCached()
    {
        $this->expectOutputString('');
        $template = 'themedir/empty.phtml';
        $this->block->setCacheLifetime(3600);
        $this->block->setCacheKey('probe');
        $this->validator->expects($this->once())
            ->method('isValid')
            ->with($template)
            ->willReturn(true);
        $this->templateEngine->expects($this->once())->method('render')->willReturn('');
        $this->cacheState->expects($this->any())->method('isEnabled')->willReturn(true);
        $this->cache->expects($this->once())
            ->method('save')
            ->with('', Template::CUSTOM_CACHE_KEY_PREFIX . 'probe', $this->anything(), 3600);

        $this->assertEquals('', $this->block->fetchView($template));
        $this->assertSame($this->block, $this->invokeSaveCache(''));
    }

    /**
     * A block may render several templates. Once one of them has failed the rendering is incomplete,
     * so the output is not cached even though it is no longer empty.
     */
    public function testRenderIsNotCachedWhenAnEarlierTemplateFailed()
    {
        $this->expectOutputString('');
        $output = '<p>Rendered</p>';
        $this->block->setCacheLifetime(3600);
        $this->block->setCacheKey('probe');
        $this->validator->method('isValid')
            ->willReturnMap([['missing.phtml', false], ['themedir/template.phtml', true]]);
        $this->appState->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_PRODUCTION);
        $this->loggerMock->expects($this->once())->method('critical');
        $this->templateEngine->expects($this->once())->method('render')->willReturn($output);
        $this->cacheState->expects($this->any())->method('isEnabled')->willReturn(true);
        $this->cache->expects($this->never())->method('save');

        $this->assertEquals('', $this->block->fetchView('missing.phtml'));
        $this->assertEquals($output, $this->block->fetchView('themedir/template.phtml'));
        $this->assertFalse($this->invokeSaveCache($output));
    }

    /**
     * @param string $data
     * @return Template|false
     */
    private function invokeSaveCache($data)
    {
        return (new \ReflectionMethod(Template::class, '_saveCache'))->invoke($this->block, $data);
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
