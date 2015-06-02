<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Test\Unit\Model\Template;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Email\Model\Template\Filter
     */
    protected $filter;

    /**
     * @var $string \Magento\Framework\Stdlib\String
     */
    private $string;

    /**
     * @var $logger \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var $escaper \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var $assetRepo \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var $scopeConfig \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var $coreVariableFactory \Magento\Variable\Model\VariableFactory
     */
    private $coreVariableFactory;

    /**
     * @var $storeManager \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var $layout \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var $layoutFactory \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var $appState \Magento\Framework\App\State
     */
    private $appState;

    /**
     * @var $backendUrlBuilder \Magento\Backend\Model\UrlInterface
     */
    private $backendUrlBuilder;


    protected function setUp()
    {
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

        $this->filter = new \Magento\Email\Model\Template\Filter(
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
            []
        );
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
        $this->filter->setVariables($variables);
        $this->assertEquals($expected, $this->filter->filter($value));
    }

    /**
     * Data provider for various possible {{trans ...}} usages
     *
     * @return array
     */
    public function transDirectiveDataProvider() {
        return [
            [   // empty string
                '{{trans}}',
                '',
            ],

            [   // given empty string
                '{{trans ""}}',
                '',
            ],

            [   // no padding
                '{{trans"Hello cruel coder..."}}',
                'Hello cruel coder...',
            ],

            [   // excessive padding
                "{{trans \t\n\r'Hello cruel coder...' \t\n\r}}",
                'Hello cruel coder...',
            ],

            [   // capture escaped double-quotes inside text
                '{{trans "Hello \"tested\" world!"}}',
                'Hello &quot;tested&quot; world!',
            ],

            [   // capture escaped single-quotes inside text
                "{{trans 'Hello \\'tested\\' world!'|escape}}",
                "Hello &#039;tested&#039; world!",
            ],

            [   // basic var
                '{{trans "Hello %adjective world!" adjective="tested"}}',
                'Hello tested world!',
            ],

            [   // auto-escaped output
                '{{trans "Hello %adjective <strong>world</strong>!" adjective="<em>bad</em>"}}',
                'Hello &lt;em&gt;bad&lt;/em&gt; &lt;strong&gt;world&lt;/strong&gt;!',
            ],

            [   // unescaped modifier
                '{{trans "Hello %adjective <strong>world</strong>!" adjective="<em>bad</em>"|raw}}',
                'Hello <em>bad</em> <strong>world</strong>!',
            ],

            [   // variable replacement
                '{{trans "Hello %adjective world!" adjective="$mood"}}',
                'Hello happy world!',
                [
                    'mood' => 'happy'
                ],
            ],
        ];
    }
}
