<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Design\Config;

use Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Theme\Model\Design\Config\DataLoader;
use Magento\Theme\Model\Design\Config\DataProvider;
use Magento\Theme\Model\Design\Config\MetadataLoader;
use Magento\Theme\Model\ResourceModel\Design\Config\Collection;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataProvider
     */
    protected $model;

    /**
     * @var DataProvider\DataLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataLoader;

    /**
     * @var DataProvider\MetadataLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataLoader;

    /**
     * @var Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var ScopeCodeResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeCodeResolverMock;

    /**
     * @var SettingChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $settingCheckerMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->dataLoader = $this->getMockBuilder('Magento\Theme\Model\Design\Config\DataProvider\DataLoader')
            ->disableOriginalConstructor()
            ->getMock();

        $this->metadataLoader = $this->getMockBuilder('Magento\Theme\Model\Design\Config\DataProvider\MetadataLoader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataLoader->expects($this->once())
            ->method('getData')
            ->willReturn([]);

        $this->collection = $this->getMockBuilder('Magento\Theme\Model\ResourceModel\Design\Config\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $collectionFactory = $this->getMockBuilder('Magento\Theme\Model\ResourceModel\Design\Config\CollectionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->collection);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeCodeResolverMock = $this->getMockBuilder(ScopeCodeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->settingCheckerMock = $this->getMockBuilder(SettingChecker::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new DataProvider(
            'scope',
            'scope',
            'scope',
            $this->dataLoader,
            $this->metadataLoader,
            $collectionFactory
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->model,
            'request',
            $this->requestMock
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->model,
            'scopeCodeResolver',
            $this->scopeCodeResolverMock
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->model,
            'settingChecker',
            $this->settingCheckerMock
        );
    }

    public function testGetData()
    {
        $data = [
            'test_key' => 'test_value',
        ];

        $this->dataLoader->expects($this->once())
            ->method('getData')
            ->willReturn($data);

        $this->assertEquals($data, $this->model->getData());
    }

    /**
     * @param array $inputMeta
     * @param array $expectedMeta
     * @param array $request
     * @dataProvider getMetaDataProvider
     */
    public function testGetMeta(array $inputMeta, array $expectedMeta, array $request)
    {
        $this->requestMock->expects($this->any())
            ->method('getParams')
            ->willReturn($request);
        $this->scopeCodeResolverMock->expects($this->any())
            ->method('resolve')
            ->with('stores', 1)
            ->willReturn('default');
        $this->settingCheckerMock->expects($this->any())
            ->method('isReadOnly')
            ->withConsecutive(
                ['design/head/welcome', 'stores', 'default'],
                ['design/head/logo', 'stores', 'default'],
                ['design/head/head', 'stores', 'default']
            )
            ->willReturnOnConsecutiveCalls(
                true,
                false,
                true
            );

        $this->objectManager->setBackwardCompatibleProperty(
            $this->model,
            'meta',
            $inputMeta
        );

        $this->assertSame($expectedMeta, $this->model->getMeta());
    }

    /**
     * @return array
     */
    public function getMetaDataProvider()
    {
        return [
            [
                [
                    'option1'
                ],
                [
                    'option1'
                ],
                [
                    'scope' => 'default'
                ]
            ],
            [
                [
                    'other_settings' => [
                        'children' => [
                            'head' => [
                                'children' => [
                                    'head_welcome' => [

                                    ],
                                    'head_logo' => [

                                    ],
                                    'head_head' => [

                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'other_settings' => [
                        'children' => [
                            'head' => [
                                'children' => [
                                    'head_welcome' => [
                                        'arguments' => [
                                            'data' => [
                                                'config' => [
                                                    'disabled' => true,
                                                    'is_disable_inheritance' => true,
                                                ]
                                            ]
                                        ]
                                    ],
                                    'head_logo' => [

                                    ],
                                    'head_head' => [
                                        'arguments' => [
                                            'data' => [
                                                'config' => [
                                                    'disabled' => true,
                                                    'is_disable_inheritance' => true,
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'scope' => 'stores',
                    'scope_id' => 1
                ]
            ]
        ];
    }
}
