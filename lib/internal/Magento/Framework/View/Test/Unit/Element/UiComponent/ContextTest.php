<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test for view Messages model
 */
namespace Magento\Framework\View\Test\Unit\Element\UiComponent;

use Magento\Framework\App\Request\Http;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContentType\ContentTypeFactory;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ActionPoolFactory;
use Magento\Framework\View\Element\UiComponent\Control\ActionPoolInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderFactory;
use Magento\Framework\View\Element\UiComponent\Processor;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContextTest extends TestCase
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ActionPoolInterface
     */
    private $actionPool;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var LayoutInterface
     */
    private $pageLayout;

    /**
     * @var ButtonProviderFactory
     */
    private $buttonProviderFactory;

    /**
     * @var ActionPoolFactory
     */
    private $actionPoolFactory;

    /**
     * @var ContentTypeFactory
     */
    private $contentTypeFactory;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var UiComponentFactory
     */
    private $uiComponentFactory;

    protected function setUp(): void
    {
        $this->pageLayout = $this->getMockBuilder(LayoutInterface::class)
            ->getMock();
        $request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHeader'])
            ->getMock();
        $request->method('getHeader')->willReturn('');
        $this->buttonProviderFactory =
            $this->getMockBuilder(ButtonProviderFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->actionPoolFactory =
            $this->getMockBuilder(ActionPoolFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->actionPool = $this->getMockBuilder(ActionPoolInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->actionPoolFactory->method('create')->willReturn($this->actionPool);
        $this->contentTypeFactory =
            $this->getMockBuilder(ContentTypeFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->getMock();
        $this->processor = $this->getMockBuilder(Processor::class)
            ->getMock();
        $this->uiComponentFactory =
            $this->getMockBuilder(UiComponentFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->authorization = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->context = $objectManagerHelper->getObject(
            Context::class,
            [
                'pageLayout'            => $this->pageLayout,
                'request'               => $request,
                'buttonProviderFactory' => $this->buttonProviderFactory,
                'actionPoolFactory'     => $this->actionPoolFactory,
                'contentTypeFactory'    => $this->contentTypeFactory,
                'urlBuilder'            => $this->urlBuilder,
                'processor'             => $this->processor,
                'uiComponentFactory'    => $this->uiComponentFactory,
                'authorization'         => $this->authorization,
            ]
        );
    }

    public function testAddButtonWithoutAclResource()
    {
        $component = $this->getMockBuilder(UiComponentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->actionPool->expects($this->once())->method('add');
        $this->authorization->expects($this->never())->method('isAllowed');

        $this->context->addButtons([
            'button_1' => [
                'name' => 'button_1',
            ],
        ], $component);
    }

    public function testAddButtonWithAclResourceAllowed()
    {
        $component = $this->getMockBuilder(UiComponentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->actionPool->expects($this->once())->method('add');
        $this->authorization->expects($this->once())->method('isAllowed')->willReturn(true);

        $this->context->addButtons([
            'button_1' => [
                'name' => 'button_1',
                'aclResource' => 'Magento_Framwork::acl',
            ],
        ], $component);
    }

    public function testAddButtonWithAclResourceDenied()
    {
        $component = $this->getMockBuilder(UiComponentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->actionPool->expects($this->never())->method('add');
        $this->authorization->expects($this->once())->method('isAllowed')->willReturn(false);

        $this->context->addButtons([
            'button_1' => [
                'name' => 'button_1',
                'aclResource' => 'Magento_Framwork::acl',
            ],
        ], $component);
    }

    /**
     * @dataProvider addComponentDefinitionDataProvider
     * @param array $components
     * @param array $expected
     */
    public function testAddComponentDefinition($components, $expected)
    {
        foreach ($components as $component) {
            $this->context->addComponentDefinition($component['name'], $component['config']);
        }
        $this->assertEquals($expected, $this->context->getComponentsDefinitions());
    }

    /**
     * @param string $headerAccept
     * @param string $acceptType
     *
     * @dataProvider getAcceptTypeDataProvider
     */
    public function testGetAcceptType($headerAccept, $acceptType)
    {
        $request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHeader'])
            ->getMock();
        $request->method('getHeader')
            ->with('Accept')
            ->willReturn($headerAccept);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $context = $objectManagerHelper->getObject(
            Context::class,
            [
                'pageLayout'            => $this->pageLayout,
                'request'               => $request,
                'buttonProviderFactory' => $this->buttonProviderFactory,
                'actionPoolFactory'     => $this->actionPoolFactory,
                'contentTypeFactory'    => $this->contentTypeFactory,
                'urlBuilder'            => $this->urlBuilder,
                'processor'             => $this->processor,
                'uiComponentFactory'    => $this->uiComponentFactory,
                'authorization'         => $this->authorization,
            ]
        );

        $this->assertEquals($acceptType, $context->getAcceptType());
    }

    /**
     * @return array
     */
    public function getAcceptTypeDataProvider()
    {
        return [
            ['json', 'json'],
            ['text/html,application/xhtml+xml,application/json;q=0.9,
            application/javascript;q=0.9,text/javascript;q=0.9,application/xml;q=0.9,
            text/plain;q=0.8,*/*;q=0.7', 'html'],
            ['application/json, text/javascript, */*;q=0.01', 'json'],
            ['text/html, application/xhtml+xml, application/xml;q=0.9,
            image/avif, image/webp, image/apng, */*;q=0.8,
            application/signed-exchange;v=b3;q=0.9', 'html'],
            ['xml', 'xml'],
            ['text/html, application/json', 'json']
        ];
    }

    /**
     * @return array
     */
    public function addComponentDefinitionDataProvider()
    {
        return [
            [
                [
                    [
                        'name' => 'component_1_Name',
                        'config' => [
                            'component_1_config_name_1' => 'component_1_config_value_1',
                            'component_1_config_name_2' => [
                                'component_1_config_value_1',
                                'component_1_config_value_2',
                                'component_1_config_value_3',
                            ],
                            'component_1_config_name_3' => 'component_1_config_value_1'
                        ]
                    ],
                    [
                        'name' => 'component_2_Name',
                        'config' => [
                            'component_2_config_name_1' => 'component_2_config_value_1',
                            'component_2_config_name_2' => [
                                'component_2_config_value_1',
                                'component_2_config_value_2',
                                'component_2_config_value_3',
                            ],
                            'component_2_config_name_3' => 'component_2_config_value_1'
                        ]
                    ],
                    [
                        'name' => 'component_1_Name',
                        'config' => [
                            'component_1_config_name_4' => 'component_1_config_value_1',
                            'component_1_config_name_5' => [
                                'component_1_config_value_1',
                                'component_1_config_value_2',
                                'component_1_config_value_3',
                            ],
                            'component_1_config_name_6' => 'component_1_config_value_1'
                        ]
                    ],
                ],
                [
                    'component_1_Name' => [
                        'component_1_config_name_1' => 'component_1_config_value_1',
                        'component_1_config_name_2' => [
                            'component_1_config_value_1',
                            'component_1_config_value_2',
                            'component_1_config_value_3',
                        ],
                        'component_1_config_name_3' => 'component_1_config_value_1',
                        'component_1_config_name_4' => 'component_1_config_value_1',
                        'component_1_config_name_5' => [
                            'component_1_config_value_1',
                            'component_1_config_value_2',
                            'component_1_config_value_3',
                        ],
                        'component_1_config_name_6' => 'component_1_config_value_1'
                    ],
                    'component_2_Name' => [
                        'component_2_config_name_1' => 'component_2_config_value_1',
                        'component_2_config_name_2' => [
                            'component_2_config_value_1',
                            'component_2_config_value_2',
                            'component_2_config_value_3',
                        ],
                        'component_2_config_name_3' => 'component_2_config_value_1'
                    ]
                ]
            ]
        ];
    }
}
