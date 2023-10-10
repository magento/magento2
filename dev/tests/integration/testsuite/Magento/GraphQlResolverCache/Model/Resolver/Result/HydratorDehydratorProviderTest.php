<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result;

use Magento\CatalogGraphQl\Model\Resolver\Cache\Product\MediaGallery\ProductModelHydrator;
use Magento\Framework\DataObject;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class HydratorDehydratorProviderTest extends TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var HydratorDehydratorProvider
     */
    private $provider;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->provider = $this->objectManager->create(
            HydratorDehydratorProvider::class,
            $this->getTestProviderConfig()
        );
        parent::setUp();
    }

    /**
     * @return array
     */
    private function getTestProviderConfig()
    {
        return [
            'hydratorConfig' => [
                'Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver' => [
                    'nested_items_hydrator' => [
                        'sortOrder' => 15,
                        'class' => 'TestResolverNestedItemsHydrator'
                    ],
                ],
                'StoreConfigResolverDerivedMock' => [
                    'model_hydrator' => [
                        'sortOrder' => 10,
                        'class' => 'TestResolverModelHydrator'
                    ],
                ]
            ],
            'dehydratorConfig' => [
                'Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver' => [
                    'simple_dehydrator' => [
                        'sortOrder' => 10,
                        'class' => 'TestResolverModelDehydrator'
                    ],
                ]
            ]
        ];
    }

    /**
     * @magentoAppArea graphql
     *
     * @return void
     */
    public function testHydratorChainProvider()
    {
        $resolver = $this->getMockBuilder(StoreConfigResolver::class)
            ->disableOriginalConstructor()
            ->setMockClassName('StoreConfigResolverDerivedMock')
            ->getMockForAbstractClass();

        $testResolverData = [
            'id' => 2,
            'name' => 'test name',
            'model' => new DataObject(
                [
                    'some_field' => 'some_data_value',
                    'id' => 2,
                    'name' => 'test name',
                ]
            )
        ];

        $testModelDehydrator = $this->getMockBuilder(DehydratorInterface::class)
            ->disableOriginalConstructor()
            ->setMockClassName('TestResolverModelDehydrator')
            ->onlyMethods(['dehydrate'])
            ->getMock();

        $testModelDehydrator->expects($this->once())
            ->method('dehydrate')
            ->willReturnCallback(function (&$resolverData) {
                $resolverData['model_data'] = $resolverData['model']->getData();
                unset($resolverData['model']);
            });

        $testModelHydrator = $this->getMockBuilder(ProductModelHydrator::class)
            ->disableOriginalConstructor()
            ->setMockClassName('TestResolverModelHydrator')
            ->onlyMethods(['hydrate', 'prehydrate'])
            ->getMock();
        $testModelHydrator->expects($this->once())
            ->method('hydrate')
            ->willReturnCallback(function (&$resolverData) {
                $do = new DataObject($resolverData['model_data']);
                $resolverData['model'] = $do;
                $resolverData['sortOrderTest_field'] = 'some data';
            });
        $testNestedHydrator = $this->getMockBuilder(HydratorInterface::class)
            ->disableOriginalConstructor()
            ->setMockClassName('TestResolverNestedItemsHydrator')
            ->onlyMethods(['hydrate'])
            ->getMock();
        $testNestedHydrator->expects($this->once())
            ->method('hydrate')
            ->willReturnCallback(function (&$resolverData) {
                $resolverData['model']->setData('nested_data', ['test_nested_data']);
                $resolverData['sortOrderTest_field'] = 'other data';
            });

        $this->objectManager->addSharedInstance($testModelHydrator, 'TestResolverModelHydrator');
        $this->objectManager->addSharedInstance($testNestedHydrator, 'TestResolverNestedItemsHydrator');
        $this->objectManager->addSharedInstance($testModelDehydrator, 'TestResolverModelDehydrator');

        $dehydrator = $this->provider->getDehydratorForResolver($resolver);
        $dehydrator->dehydrate($testResolverData);

        /** @var HydratorInterface $hydrator */
        $hydrator = $this->provider->getHydratorForResolver($resolver);
        $hydrator->hydrate($testResolverData);

        // assert that data object is instantiated
        $this->assertInstanceOf(DataObject::class, $testResolverData['model']);
        // assert object fields
        $this->assertEquals(2, $testResolverData['model']->getId());
        $this->assertEquals('test name', $testResolverData['model']->getName());
        // assert mode nested data from second hydrator
        $this->assertEquals(['test_nested_data'], $testResolverData['model']->getNestedData());
        $this->assertEquals('some_data_value', $testResolverData['model']->getData('some_field'));

        //verify that hydrators were invoked in designated order
        $this->assertEquals('other data', $testResolverData['sortOrderTest_field']);

        // verify that hydrator instance is not recreated
        $this->assertSame($hydrator, $this->provider->getHydratorForResolver($resolver));

        $this->objectManager->removeSharedInstance('TestResolverModelHydrator');
        $this->objectManager->removeSharedInstance('TestResolverNestedItemsHydrator');
        $this->objectManager->removeSharedInstance('TestResolverModelDehydrator');
    }

    /**
     * @magentoAppArea graphql
     *
     * @return void
     */
    public function testHydratorDoesNotExist()
    {
        $resolver = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->assertNull($this->provider->getHydratorForResolver($resolver));
    }

    /**
     * @magentoAppArea graphql
     *
     * @return void
     */
    public function testHydratorClassMismatch()
    {
        $this->expectExceptionMessage('Hydrator TestResolverModelDehydrator configured for resolver '
            . 'Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver must implement '
            . 'Magento\GraphQlResolverCache\Model\Resolver\Result\HydratorInterface.');
        $testModelDehydrator = $this->getMockBuilder(DehydratorInterface::class)
            ->disableOriginalConstructor()
            ->setMockClassName('TestResolverModelDehydrator')
            ->onlyMethods(['dehydrate'])
            ->getMock();
        $this->objectManager->addSharedInstance($testModelDehydrator, 'TestResolverModelDehydrator');

        $this->provider = $this->objectManager->create(
            HydratorDehydratorProvider::class,
            [
                'hydratorConfig' => [
                    'Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver' => [
                        'simple_dehydrator' => [
                            'sortOrder' => 10,
                            'class' => 'TestResolverModelDehydrator'
                        ],
                    ]
                ]
            ]
        );
        $resolver = $this->getMockBuilder(StoreConfigResolver::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->assertNull($this->provider->getHydratorForResolver($resolver));
    }

    /**
     * @magentoAppArea graphql
     *
     * @return void
     */
    public function testDehydratorClassMismatch()
    {
        $this->expectExceptionMessage('Dehydrator TestResolverModelHydrator configured for resolver '
            . 'Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver must implement '
            . 'Magento\GraphQlResolverCache\Model\Resolver\Result\DehydratorInterface.');
        $hydrator = $this->getMockBuilder(HydratorInterface::class)
            ->disableOriginalConstructor()
            ->setMockClassName('TestResolverModelHydrator')
            ->getMock();
        $this->objectManager->addSharedInstance($hydrator, 'TestResolverModelHydrator');

        $this->provider = $this->objectManager->create(
            HydratorDehydratorProvider::class,
            [
                'dehydratorConfig' => [
                    'Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver' => [
                        'simple_dehydrator' => [
                            'sortOrder' => 10,
                            'class' => 'TestResolverModelHydrator'
                        ],
                    ]
                ]
            ]
        );
        $resolver = $this->getMockBuilder(StoreConfigResolver::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->assertNull($this->provider->getDehydratorForResolver($resolver));
    }

    /**
     * @magentoAppArea graphql
     *
     * @return void
     */
    public function testDehydratorDoesNotExist()
    {
        $resolver = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->assertNull($this->provider->getDehydratorForResolver($resolver));
    }
}
