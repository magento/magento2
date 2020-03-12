<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Email\Test\Unit\Model\Template;

use Magento\Email\Model\Template\Css\Processor;
use Magento\Email\Model\Template\Filter;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Asset\File\FallbackContext;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class FilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    private $string;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    private $escaper;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepo;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Variable\Model\VariableFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $coreVariableFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layout;

    /**
     * @var \Magento\Framework\View\LayoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutFactory;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appState;

    /**
     * @var \Magento\Backend\Model\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $backendUrlBuilder;

    /**
     * @var \Magento\Variable\Model\Source\Variables|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configVariables;

    /**
     * @var \Pelago\Emogrifier
     */
    private $emogrifier;

    /**
     * @var \Magento\Framework\Css\PreProcessor\Adapter\CssInliner
     */
    private $cssInliner;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Email\Model\Template\Css\Processor
     */
    private $cssProcessor;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Filesystem
     */
    private $pubDirectory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Filesystem\Directory\Read
     */
    private $pubDirectoryRead;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Filter\VariableResolver\StrategyResolver
     */
    private $variableResolver;

    /**
     * @var array
     */
    private $directiveProcessors;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->string = $this->getMockBuilder(\Magento\Framework\Stdlib\StringUtils::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaper = $this->objectManager->getObject(\Magento\Framework\Escaper::class);

        $this->assetRepo = $this->getMockBuilder(\Magento\Framework\View\Asset\Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->coreVariableFactory = $this->getMockBuilder(\Magento\Variable\Model\VariableFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layout = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutFactory = $this->getMockBuilder(\Magento\Framework\View\LayoutFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->appState = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->backendUrlBuilder = $this->getMockBuilder(\Magento\Backend\Model\UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->emogrifier = $this->objectManager->getObject(\Pelago\Emogrifier::class);

        $this->configVariables = $this->getMockBuilder(\Magento\Variable\Model\Source\Variables::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cssInliner = $this->objectManager->getObject(
            \Magento\Framework\Css\PreProcessor\Adapter\CssInliner::class
        );

        $this->cssProcessor = $this->getMockBuilder(\Magento\Email\Model\Template\Css\Processor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pubDirectory = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->pubDirectoryRead = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\Read::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->variableResolver =
            $this->getMockBuilder(\Magento\Framework\Filter\VariableResolver\StrategyResolver::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->directiveProcessors = [
            'depend' => $this->getMockBuilder(\Magento\Framework\Filter\DirectiveProcessor\DependDirective::class)
                    ->disableOriginalConstructor()
                    ->getMock(),
            'if' => $this->getMockBuilder(\Magento\Framework\Filter\DirectiveProcessor\IfDirective::class)
                    ->disableOriginalConstructor()
                    ->getMock(),
            'template' => $this->getMockBuilder(\Magento\Framework\Filter\DirectiveProcessor\TemplateDirective::class)
                    ->disableOriginalConstructor()
                    ->getMock(),
            'legacy' => $this->getMockBuilder(\Magento\Framework\Filter\DirectiveProcessor\LegacyDirective::class)
                    ->disableOriginalConstructor()
                    ->getMock(),
        ];
    }

    /**
     * @param array|null $mockedMethods Methods to mock
     * @return Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getModel($mockedMethods = null)
    {
        return $this->getMockBuilder(\Magento\Email\Model\Template\Filter::class)
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
                    $this->emogrifier,
                    $this->configVariables,
                    [],
                    $this->cssInliner,
                    $this->directiveProcessors,
                    $this->variableResolver,
                    $this->cssProcessor,
                    $this->pubDirectory
                ]
            )
            ->setMethods($mockedMethods)
            ->getMock();
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
            ->will($this->returnValue($css));

        $designParams = [
            'area' => Area::AREA_FRONTEND,
            'theme' => 'themeId',
            'locale' => 'localeId',
        ];
        $filter->setDesignParams($designParams);

        foreach ($expectedResults as $expectedResult) {
            $this->assertContains($expectedResult, $filter->applyInlineCss($html));
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

        $asset = $this->getMockBuilder(\Magento\Framework\View\Asset\File::class)
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
    public function applyInlineCssDataProvider()
    {
        return [
            'Ensure styles get inlined' => [
                '<html><p></p></html>',
                'p { color: #000 }',
                ['<p style="color: #000;"></p>'],
            ],
            'CSS with error does not get inlined' => [
                '<html><p></p></html>',
                \Magento\Framework\View\Asset\ContentProcessorInterface::ERROR_MESSAGE_PREFIX,
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

    /**
     * @expectedException \Magento\Framework\Exception\MailException
     */
    public function testApplyInlineCssThrowsExceptionWhenDesignParamsNotSet()
    {
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
        $availableConfigs = [['value' => $path]];
        $construction = ["{{config path={$path}}}", 'config', " path={$path}"];
        $scopeConfigValue = 'value';

        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getId')->willReturn(1);

        $this->configVariables->expects($this->once())
            ->method('getData')
            ->willReturn($availableConfigs);
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->willReturn($scopeConfigValue);

        $this->assertEquals($scopeConfigValue, $this->getModel()->configDirective($construction));
    }

    public function testConfigDirectiveUnavailable()
    {
        $path = "web/unsecure/base_url";
        $availableConfigs = [];
        $construction = ["{{config path={$path}}}", 'config', " path={$path}"];
        $scopeConfigValue = '';

        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($storeMock);
        $storeMock->expects($this->once())->method('getId')->willReturn(1);

        $this->configVariables->expects($this->once())
            ->method('getData')
            ->willReturn($availableConfigs);
        $this->scopeConfig->expects($this->never())
            ->method('getValue')
            ->willReturn($scopeConfigValue);

        $this->assertEquals($scopeConfigValue, $this->getModel()->configDirective($construction));
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
     * @expectedException \Magento\Framework\Exception\MailException
     * @throws NoSuchEntityException
     */
    public function testProtocolDirectiveWithInvalidSchema()
    {
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
}
