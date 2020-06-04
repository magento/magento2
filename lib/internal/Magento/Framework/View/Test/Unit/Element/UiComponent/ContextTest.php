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

    protected function setUp(): void
    {
        $pageLayout = $this->getMockBuilder(LayoutInterface::class)
            ->getMock();
        $request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $buttonProviderFactory =
            $this->getMockBuilder(ButtonProviderFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $actionPoolFactory =
            $this->getMockBuilder(ActionPoolFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $this->actionPool = $this->getMockBuilder(ActionPoolInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $actionPoolFactory->method('create')->willReturn($this->actionPool);
        $contentTypeFactory =
            $this->getMockBuilder(ContentTypeFactory::class)
                ->disableOriginalConstructor()
                ->getMock();
        $urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->getMock();
        $processor = $this->getMockBuilder(Processor::class)
            ->getMock();
        $uiComponentFactory =
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
                'pageLayout'            => $pageLayout,
                'request'               => $request,
                'buttonProviderFactory' => $buttonProviderFactory,
                'actionPoolFactory'     => $actionPoolFactory,
                'contentTypeFactory'    => $contentTypeFactory,
                'urlBuilder'            => $urlBuilder,
                'processor'             => $processor,
                'uiComponentFactory'    => $uiComponentFactory,
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
