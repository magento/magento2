<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model\Template;

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

        $this->string = $this->getMockBuilder('\Magento\Framework\Stdlib\StringUtils')
            ->disableOriginalConstructor()
            ->getMock();

        $this->logger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaper = $this->getMockBuilder('\Magento\Framework\Escaper')
            ->disableOriginalConstructor()
            ->enableProxyingToOriginalMethods()
            ->getMock();

        $this->assetRepo = $this->getMockBuilder('\Magento\Framework\View\Asset\Repository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder('\Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->coreVariableFactory = $this->getMockBuilder('\Magento\Variable\Model\VariableFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder('\Magento\Store\Model\StoreManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->layout = $this->getMockBuilder('\Magento\Framework\View\LayoutInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutFactory = $this->getMockBuilder('\Magento\Framework\View\LayoutFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->appState = $this->getMockBuilder('\Magento\Framework\App\State')
            ->disableOriginalConstructor()
            ->getMock();

        $this->backendUrlBuilder = $this->getMockBuilder('\Magento\Backend\Model\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emogrifier = $this->objectManager->getObject('\Pelago\Emogrifier');

        $this->configVariables = $this->getMockBuilder('Magento\Email\Model\Source\Variables')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array|null $mockedMethods Methods to mock
     * @return \Magento\Email\Model\Template\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getModel($mockedMethods = null)
    {
        return $this->getMockBuilder('\Magento\Email\Model\Template\Filter')
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

        $filter->expects($this->exactly(count($expectedResults)))
            ->method('getCssFilesContent')
            ->will($this->returnValue($css));

        $designParams = [
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'theme' => 'themeId',
            'locale' => 'localeId',
        ];
        $filter->setDesignParams($designParams);

        foreach ($expectedResults as $expectedResult) {
            $this->assertContains($expectedResult, $filter->applyInlineCss($html));
        }
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
        $this->getModel()->applyInlineCss('test');
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

        $storeMock = $this->getMock('Magento\Store\Api\Data\StoreInterface', [], [], '', false);
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

        $storeMock = $this->getMock('Magento\Store\Api\Data\StoreInterface', [], [], '', false);
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
