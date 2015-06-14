<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model\Template;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Email\Model\Template\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filter;

    /**
     * @var \Magento\Framework\Stdlib\String|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Pelago\Emogrifier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $emogrifier;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->string = $this->getMockBuilder('\Magento\Framework\Stdlib\String')
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
    }

    /**
     * @param null $mockedMethods Methods to mock
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
                []
            ])
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
    public function testApplyInlineCss($html, $css, $expectedResults){
        /* @var $filter \Magento\Email\Model\Template\Filter */
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
                \Magento\Framework\Css\PreProcessor\Adapter\Oyejorge::ERROR_MESSAGE_PREFIX,
                ['<html><p></p></html>'],
            ],
            'Ensure disableStyleBlocksParsing option is working' => [
                '<html><head><style type="text/css">div { color: #111; }</style></head><p></p></html>',
                'p { color: #000 }',
                [
                    '<head><style type="text/css">div { color: #111; }</style></head>',
                    '<p style="color: #000;"></p>',
                ]
            ],
        ];
    }

    /**
     * @expectedException \LogicException
     */
    public function testApplyInlineCssThrowsExceptionWhenDesignParamsNotSet()
    {
        $this->getModel()->applyInlineCss('test');
    }
}
