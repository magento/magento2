<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\View\Element;

use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Config\DataInterface as ConfigData;
use Magento\Framework\Config\DataInterfaceFactory as ConfigDataFactory;

/**
 * Test the component factory.
 */
class UiComponentFactoryTest extends TestCase
{
    /**
     * @var UiComponentFactoryFactory
     */
    private $factory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->factory = Bootstrap::getObjectManager()->get(UiComponentFactoryFactory::class);
    }

    /**
     * Create factory with mock config provided.
     *
     * @param array $mockConfig
     * @return UiComponentFactory
     */
    private function createFactory(array $mockConfig): UiComponentFactory
    {
        $dataMock = $this->getMockForAbstractClass(ConfigData::class);
        $dataMock->method('get')->willReturnCallback(
            function (string $id) use ($mockConfig) : array {
                return $mockConfig[$id];
            }
        );
        $dataFactoryMock = $this->getMockBuilder(ConfigDataFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dataFactoryMock->method('create')->willReturn($dataMock);

        return $this->factory->create(['configFactory' => $dataFactoryMock]);
    }

    /**
     * Test creating a component.
     *
     * @return void
     * @magentoAppArea adminhtml
     */
    public function testCreate(): void
    {
        //Mocking component config.
        $factory = $this->createFactory([
            'test' => [
                'arguments' => ['data' => ['config' => ['component' => 'uiComponent']]],
                'attributes' => [
                    'name' => 'test',
                    'sorting' => true,
                    'class' => 'Magento\Ui\Component\Listing',
                    'component' => 'uiComponent'
                ],
                'children' => [
                    'test_child' => [
                        'arguments' => [
                            'data' => ['config' => ['component' => 'uiComponent']],
                            'dataProvider' => $this->generateMockProvider()
                        ],
                        'attributes' => [
                            'name' => 'test_child',
                            'sorting' => true,
                            'class' => 'Magento\Ui\Component\Listing',
                            'component' => 'uiComponent'
                        ],
                        'children' => []
                    ]
                ]
            ],
            'test_child_child' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'component' => 'uiComponent',
                            'label' => '${\'Label\'}',
                            'componentType' => 'component'
                        ]
                    ],
                ],
                'attributes' => [
                    'name' => 'test_child_child',
                    'sorting' => true,
                    'class' => 'Magento\Ui\Component\Listing',
                    'component' => 'uiComponent'
                ],
                'children' => []
            ]
        ]);
        $component = $factory->create('test', null, ['data' => ['label' => '${\'Label\'}']]);

        $componentData = $component->getData();
        //Arguments passed must be sanitized
        $this->assertArrayHasKey('__disableTmpl', $componentData);
        $this->assertEquals(['label' => true], $componentData['__disableTmpl']);
        //Metadata provided by the dataProvider must be sanitized as well.
        $this->assertArrayHasKey('test_child_child', $childData = $component->getChildComponents());
        $childData = $component->getChildComponents()['test_child_child']->getData()['config'];
        $this->assertArrayHasKey('__disableTmpl', $childData);
        $this->assertEquals(['label' => true], $childData['__disableTmpl']);
    }

    /**
     * Generate provider for the test.
     *
     * @return DataProviderInterface
     */
    private function generateMockProvider(): DataProviderInterface
    {
        /** @var DataProviderInterface|MockObject $mock */
        $mock = $this->getMockForAbstractClass(DataProviderInterface::class);
        $mock->method('getName')->willReturn('test');
        $mock->method('getPrimaryFieldName')->willReturn('id');
        $mock->method('getRequestFieldName')->willReturn('id');
        $mock->method('getData')->willReturn([]);
        $mock->method('getConfigData')->willReturn([]);
        $mock->method('getFieldMetaInfo')->willReturn([]);
        $mock->method('getFieldSetMetaInfo')->willReturn('id');
        $mock->method('getFieldsMetaInfo')->willReturn('id');
        $mock->method('getSearchCriteria')->willReturn(new SearchCriteria());
        $mock->method('getSearchResult')->willReturn([]);
        $mock->method('getMeta')->willReturn(
            [
                'test_child_child' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'component' => 'uiComponent',
                                'label' => '${\'Label\'}',
                                'componentType' => 'component'
                            ]
                        ],
                    ],
                    'attributes' => [
                        'name' => 'test_child_child',
                        'sorting' => true,
                        'class' => 'Magento\Ui\Component\Listing',
                        'component' => 'uiComponent'
                    ]
                ]
            ]
        );

        return $mock;
    }
}
