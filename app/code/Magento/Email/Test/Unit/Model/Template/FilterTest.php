<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Email\Test\Unit\Model\Template;

use Magento\Backend\Model\Url as BackendModelUrl;
use Magento\Backend\Model\UrlInterface;
use Magento\Email\Model\Template\Css\Processor;
use Magento\Email\Model\Template\Filter;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\State;
use Magento\Framework\Css\PreProcessor\Adapter\CssInliner;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filter\DirectiveProcessor\DependDirective;
use Magento\Framework\Filter\DirectiveProcessor\IfDirective;
use Magento\Framework\Filter\DirectiveProcessor\LegacyDirective;
use Magento\Framework\Filter\DirectiveProcessor\TemplateDirective;
use Magento\Framework\Filter\VariableResolver\StrictResolver;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\ContentProcessorInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Variable\Model\Source\Variables;
use Magento\Variable\Model\VariableFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\Information as StoreInformation;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class FilterTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var StringUtils|MockObject
     */
    private $string;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var Escaper|MockObject
     */
    private $escaper;

    /**
     * @var Repository|MockObject
     */
    private $assetRepo;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfig;

    /**
     * @var VariableFactory|MockObject
     */
    private $coreVariableFactory;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var LayoutInterface|MockObject
     */
    private $layout;

    /**
     * @var LayoutFactory|MockObject
     */
    private $layoutFactory;

    /**
     * @var State|MockObject
     */
    private $appState;

    /**
     * @var UrlInterface|MockObject
     */
    private $backendUrlBuilder;

    /**
     * @var Variables|MockObject
     */
    private $configVariables;

    /**
     * @var CssInliner
     */
    private $cssInliner;

    /**
     * @var MockObject|Processor
     */
    private $cssProcessor;

    /**
     * @var MockObject|Filesystem
     */
    private $pubDirectory;

    /**
     * @var MockObject|Read
     */
    private $pubDirectoryRead;

    /**
     * @var MockObject|StrictResolver
     */
    private $variableResolver;

    /**
     * @var array
     */
    private $directiveProcessors;

    /**
     * @var StoreInformation
     */
    private $storeInformation;

    /**
     * @var store
     */
    private $store;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->string = $this->getMockBuilder(StringUtils::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->escaper = $this->objectManager->getObject(Escaper::class);

        $this->assetRepo = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->coreVariableFactory = $this->getMockBuilder(VariableFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->layout = $this->getMockBuilder(LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->layoutFactory = $this->getMockBuilder(LayoutFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->appState = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->backendUrlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->configVariables = $this->getMockBuilder(Variables::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cssInliner = $this->objectManager->getObject(
            CssInliner::class
        );

        $this->cssProcessor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pubDirectory = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pubDirectoryRead = $this->getMockBuilder(Read::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->variableResolver =
            $this->getMockBuilder(StrictResolver::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->directiveProcessors = [
            'depend' => $this->getMockBuilder(DependDirective::class)
                ->disableOriginalConstructor()
                ->getMock(),
            'if' => $this->getMockBuilder(IfDirective::class)
                ->disableOriginalConstructor()
                ->getMock(),
            'template' => $this->getMockBuilder(TemplateDirective::class)
                ->disableOriginalConstructor()
                ->getMock(),
            'legacy' => $this->getMockBuilder(LegacyDirective::class)
                ->disableOriginalConstructor()
                ->getMock(),
        ];

        $this->store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeInformation = $this->getMockBuilder(StoreInformation::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array|null $mockedMethods Methods to mock
     * @return Filter|MockObject
     */
    protected function getModel($mockedMethods = [])
    {
        $this->objectManager->prepareObjectManager([]);

        return $this->getMockBuilder(Filter::class)
            ->setConstructorArgs(
                [
                    $this->string,
                    $this->logger,
                    $this->escaper,
                    $this->assetRepo,
                    $this->scopeConfig,
                    $this->coreVariableFactory,
                    $this->storeManager,
                    $this->layout,
                    $this->layoutFactory,
                    $this->appState,
                    $this->backendUrlBuilder,
                    $this->configVariables,
                    $this->variableResolver,
                    $this->cssProcessor,
                    $this->pubDirectory,
                    $this->cssInliner,
                    [],
                    $this->directiveProcessors,
                    $this->storeInformation
                ]
            )
            ->onlyMethods($mockedMethods)
            ->getMock();
    }

    /**
     * Test exception handling of filter method
     */
    public function testFilterExceptionHandler()
    {
        $filter = $this->getModel();
        $filteredValue = $filter->filter(null);
        $this->assertTrue(is_string($filteredValue));
    }

    /**
     * Test basic usages of applyInlineCss
     *
     * @param $html
     * @param $css
     * @param $expectedResults
     *
     * @dataProvider applyInlineCssDataProvider
     */
    public function testApplyInlineCss($html, $css, $expectedResults)
    {
        $filter = $this->getModel(['getCssFilesContent']);
        $cssProcessor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $reflectionClass = new \ReflectionClass(Filter::class);
        $reflectionProperty = $reflectionClass->getProperty('cssProcessor');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($filter, $cssProcessor);
        $cssProcessor->expects($this->any())
            ->method('process')
            ->willReturnArgument(0);

        $filter->expects($this->exactly(count($expectedResults)))
            ->method('getCssFilesContent')
            ->willReturn($css);

        $designParams = [
            'area' => Area::AREA_FRONTEND,
            'theme' => 'themeId',
            'locale' => 'localeId',
        ];
        $filter->setDesignParams($designParams);

        foreach ($expectedResults as $expectedResult) {
            $this->assertStringContainsString($expectedResult, $filter->applyInlineCss($html));
        }
    }

    public function testGetCssFilesContent()
    {
        $file = 'css/email.css';
        $path = Area::AREA_FRONTEND . '/themeId/localeId';
        $css = 'p{color:black}';
        $designParams = [
            'area' => Area::AREA_FRONTEND,
            'theme' => 'themeId',
            'locale' => 'localeId',
        ];
        $filter = $this->getModel();

        $asset = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fallbackContext = $this->getMockBuilder(FallbackContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $fallbackContext->expects($this->once())
            ->method('getBaseDirType')
            ->willReturn(DirectoryList::STATIC_VIEW);
        $asset->expects($this->atLeastOnce())
            ->method('getContext')
            ->willReturn($fallbackContext);

        $asset->expects($this->atLeastOnce())
            ->method('getPath')
            ->willReturn($path . DIRECTORY_SEPARATOR . $file);
        $this->assetRepo->expects($this->once())
            ->method('createAsset')
            ->with($file, $designParams)
            ->willReturn($asset);

        $this->pubDirectory
            ->expects($this->once())
            ->method('getDirectoryRead')
            ->willReturn($this->pubDirectoryRead);

        $this->pubDirectoryRead->expects($this->once())
            ->method('isExist')
            ->with($path . DIRECTORY_SEPARATOR . $file)
            ->willReturn(true);
        $this->pubDirectoryRead->expects($this->once())
            ->method('readFile')
            ->with($path . DIRECTORY_SEPARATOR . $file)
            ->willReturn($css);

        $filter->setDesignParams($designParams);

        $this->assertEquals($css, $filter->getCssFilesContent([$file]));
    }

    /**
     * @return array
     */
    public static function applyInlineCssDataProvider()
    {
        return [
            'Ensure styles get inlined' => [
                '<html><p></p></html>',
                'p { color: #000 }',
                ['<p style="color: #000;"></p>'],
            ],
            'CSS with error does not get inlined' => [
                '<html><p></p></html>',
                ContentProcessorInterface::ERROR_MESSAGE_PREFIX,
                ['<html><p></p></html>'],
            ],
            'Ensure disableStyleBlocksParsing option is working' => [
                '<html><head><style type="text/css">div { color: #111; }</style></head><p></p></html>',
                'p { color: #000 }',
                [
                    '<style type="text/css">div { color: #111; }</style>',
                    '<p style="color: #000;"></p>',
                ],
            ],
        ];
    }

    public function testApplyInlineCssThrowsExceptionWhenDesignParamsNotSet()
    {
        $this->expectException('Magento\Framework\Exception\MailException');
        $filter = $this->getModel();
        $cssProcessor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $reflectionClass = new \ReflectionClass(Filter::class);
        $reflectionProperty = $reflectionClass->getProperty('cssProcessor');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($filter, $cssProcessor);
        $cssProcessor->expects($this->any())
            ->method('process')
            ->willReturnArgument(0);

        $filter->applyInlineCss('test');
    }

    public function testConfigDirectiveAvailable()
    {
        $path = "web/unsecure/base_url";
        $availableConfigs = ['value' => $path];
        $construction = ["{{config path={$path}}}", 'config', " path={$path}"];
        $scopeConfigValue = 'value';

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->any())->method('getId')->willReturn(1);

        $this->configVariables->expects($this->once())
            ->method('getAvailableVars')
            ->willReturn($availableConfigs);
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->willReturn($scopeConfigValue);

        $this->storeInformation->expects($this->once())
            ->method('getStoreInformationObject')
            ->willReturn(new DataObject([]));

        $this->assertEquals($scopeConfigValue, $this->getModel()->configDirective($construction));
    }

    public function testConfigDirectiveUnavailable()
    {
        $path = "web/unsecure/base_url";
        $availableConfigs = [];
        $construction = ["{{config path={$path}}}", 'config', " path={$path}"];
        $scopeConfigValue = '';

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->any())->method('getId')->willReturn(1);

        $this->configVariables->expects($this->once())
            ->method('getAvailableVars')
            ->willReturn($availableConfigs);
        $this->scopeConfig->expects($this->never())
            ->method('getValue')
            ->willReturn($scopeConfigValue);

        $this->storeInformation->expects($this->once())
            ->method('getStoreInformationObject')
            ->willReturn(new DataObject([]));

        $this->assertEquals($scopeConfigValue, $this->getModel()->configDirective($construction));
    }

    /**
     * @throws NoSuchEntityException
     */
    public function testConfigDirectiveGetCountry()
    {
        $path = "general/store_information/country_id";
        $availableConfigs = ['value' => $path];
        $construction = ["{{config path={$path}}}", 'config', " path={$path}"];
        $expectedCountry = 'United States';

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->any())->method('getId')->willReturn(1);

        $this->configVariables->expects($this->once())
            ->method('getAvailableVars')
            ->willReturn($availableConfigs);

        $this->storeInformation->expects($this->once())
            ->method('getStoreInformationObject')
            ->willReturn(new DataObject(['country_id' => 'US', 'country' => 'United States']));

        $this->assertEquals($expectedCountry, $this->getModel()->configDirective($construction));
    }

    /**
     * @throws NoSuchEntityException
     */
    public function testConfigDirectiveGetRegion()
    {
        $path = "general/store_information/region_id";
        $availableConfigs = ['value' => $path];
        $construction = ["{{config path={$path}}}", 'config', " path={$path}"];
        $expectedRegion = 'Texas';

        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->any())->method('getId')->willReturn(1);

        $this->configVariables->expects($this->once())
            ->method('getAvailableVars')
            ->willReturn($availableConfigs);

        $this->storeInformation->expects($this->once())
            ->method('getStoreInformationObject')
            ->willReturn(new DataObject(['region_id' => '57', 'region' => 'Texas']));

        $this->assertEquals($expectedRegion, $this->getModel()->configDirective($construction));
    }

    /**
     * @throws MailException
     * @throws NoSuchEntityException
     */
    public function testProtocolDirectiveWithValidSchema()
    {
        $model = $this->getModel();
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->expects($this->once())->method('isCurrentlySecure')->willReturn(true);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);

        $data = [
            "{{protocol http=\"http://url\" https=\"https://url\"}}",
            "protocol",
            " http=\"http://url\" https=\"https://url\""
        ];
        $this->assertEquals('https://url', $model->protocolDirective($data));
    }

    /**
     * @throws NoSuchEntityException
     */
    public function testProtocolDirectiveWithInvalidSchema()
    {
        $this->expectException(
            \Magento\Framework\Exception\MailException::class
        );

        $model = $this->getModel();
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->expects($this->once())->method('isCurrentlySecure')->willReturn(true);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);

        $data = [
            "{{protocol http=\"https://url\" https=\"http://url\"}}",
            "protocol",
            " http=\"https://url\" https=\"http://url\""
        ];
        $model->protocolDirective($data);
    }

    /**
     * @dataProvider dataProviderUrlModelCompanyRedirect
     */
    public function testStoreDirectiveForCompanyRedirect($className, $backendModelClass)
    {
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($this->store);
        $this->store->expects($this->any())->method('getCode')->willReturn('frvw');

        $this->backendUrlBuilder = $this->getMockBuilder($className)
            ->onlyMethods(['setScope','getUrl'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->backendUrlBuilder->expects($this->once())
            ->method('getUrl')
            ->willReturn('http://m246ceeeb2b.test/frvw/');

        if ($backendModelClass) {
            $this->backendUrlBuilder->expects($this->never())->method('setScope');
        } else {
            $this->backendUrlBuilder->expects($this->once())->method('setScope')->willReturnSelf();
        }
        $this->assertInstanceOf($className, $this->backendUrlBuilder);
        $result = $this->getModel()->storeDirective(["{{store url=''}}",'store',"url=''"]);
        $this->assertEquals('http://m246ceeeb2b.test/frvw/', $result);
    }

    /**
     * @return array[]
     */
    public static function dataProviderUrlModelCompanyRedirect(): array
    {
        return [
            [
                UrlInterface::class,
                0
            ],
            [
                BackendModelUrl::class,
                1
            ]
        ];
    }
}
