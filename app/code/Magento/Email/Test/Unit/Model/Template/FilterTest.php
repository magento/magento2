<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model\Template;

use Magento\Email\Model\Template\Css\Processor;
use Magento\Email\Model\Template\Filter;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\View\Asset\File\FallbackContext;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FilterTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Email\Model\Source\Variables|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configVariables;

    /**
     * @var \Pelago\Emogrifier
     */
    private $emogrifier;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->string = $this->getMockBuilder(\Magento\Framework\Stdlib\StringUtils::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaper = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $this->configVariables = $this->getMockBuilder(\Magento\Email\Model\Source\Variables::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array|null $mockedMethods Methods to mock
     * @return Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getModel($mockedMethods = null)
    {
        return $this->getMockBuilder(\Magento\Email\Model\Template\Filter::class)
            ->setConstructorArgs([
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
            ])
            ->setMethods($mockedMethods)
            ->getMock();
    }

    /**
     * Tests proper parsing of the {{trans ...}} directive used in email templates
     *
     * @dataProvider transDirectiveDataProvider
     * @param $value
     * @param $expected
     * @param array $variables
     */
    public function testTransDirective($value, $expected, array $variables = [])
    {
        $filter = $this->getModel()->setVariables($variables);
        $this->assertEquals($expected, $filter->filter($value));
    }

    /**
     * Data provider for various possible {{trans ...}} usages
     *
     * @return array
     */
    public function transDirectiveDataProvider()
    {
        return [
            'empty directive' => [
                '{{trans}}',
                '',
            ],

            'empty string' => [
                '{{trans ""}}',
                '',
            ],

            'no padding' => [
                '{{trans"Hello cruel coder..."}}',
                'Hello cruel coder...',
            ],

            'multi-line padding' => [
                "{{trans \t\n\r'Hello cruel coder...' \t\n\r}}",
                'Hello cruel coder...',
            ],

            'capture escaped double-quotes inside text' => [
                '{{trans "Hello \"tested\" world!"}}',
                'Hello &quot;tested&quot; world!',
            ],

            'capture escaped single-quotes inside text' => [
                "{{trans 'Hello \\'tested\\' world!'|escape}}",
                "Hello &#039;tested&#039; world!",
            ],

            'basic var' => [
                '{{trans "Hello %adjective world!" adjective="tested"}}',
                'Hello tested world!',
            ],

            'auto-escaped output' => [
                '{{trans "Hello %adjective <strong>world</strong>!" adjective="<em>bad</em>"}}',
                'Hello &lt;em&gt;bad&lt;/em&gt; &lt;strong&gt;world&lt;/strong&gt;!',
            ],

            'unescaped modifier' => [
                '{{trans "Hello %adjective <strong>world</strong>!" adjective="<em>bad</em>"|raw}}',
                'Hello <em>bad</em> <strong>world</strong>!',
            ],

            'variable replacement' => [
                '{{trans "Hello %adjective world!" adjective="$mood"}}',
                'Hello happy world!',
                [
                    'mood' => 'happy'
                ],
            ],
        ];
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

        $pubDirectory = $this->getMockBuilder(ReadInterface::class)
            ->getMockForAbstractClass();
        $reflectionClass = new \ReflectionClass(Filter::class);
        $reflectionProperty = $reflectionClass->getProperty('pubDirectory');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($filter, $pubDirectory);
        $pubDirectory->expects($this->once())
            ->method('isExist')
            ->with($path . DIRECTORY_SEPARATOR . $file)
            ->willReturn(true);
        $pubDirectory->expects($this->once())
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
                    '<head><style type="text/css">div { color: #111; }</style></head>',
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

    /**
     * Ensure that after filter callbacks are reset after exception is thrown during filtering
     */
    public function testAfterFilterCallbackGetsResetWhenExceptionTriggered()
    {
        $value = '{{var random_var}}';
        $exception = new \Exception('Test exception');
        $exceptionResult = sprintf(__('Error filtering template: %s'), $exception->getMessage());

        $this->appState->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue(\Magento\Framework\App\State::MODE_DEVELOPER));
        $this->logger->expects($this->once())
            ->method('critical')
            ->with($exception);

        $filter = $this->getModel(['varDirective', 'resetAfterFilterCallbacks']);
        $filter->expects($this->once())
            ->method('varDirective')
            ->will($this->throwException($exception));

        // Callbacks must be reset after exception is thrown
        $filter->expects($this->once())
            ->method('resetAfterFilterCallbacks');

        // Build arbitrary object to pass into the addAfterFilterCallback method
        $callbackObject = $this->getMockBuilder('stdObject')
            ->setMethods(['afterFilterCallbackMethod'])
            ->getMock();
        // Callback should never run due to exception happening during filtering
        $callbackObject->expects($this->never())
            ->method('afterFilterCallbackMethod');
        $filter->addAfterFilterCallback([$callbackObject, 'afterFilterCallbackMethod']);

        $this->assertEquals($exceptionResult, $filter->filter($value));
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
}
