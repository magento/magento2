<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result;

use Magento\Framework\DataObject;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver;
use Magento\TestFramework\Helper\Bootstrap;

class HydratorProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var HydratorProvider
     */
    private $provider;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->provider = $this->objectManager->create(HydratorProvider::class, $this->getTestProviderConfig());
        parent::setUp();
    }

    /**
     * @return array
     */
    private function getTestProviderConfig()
    {
        return [
            'resolverResultHydrators' => [
                'Magento\StoreGraphQl\Model\Resolver\StoreConfigResolver' => [
                    'nested_items_hydrator' => [
                        'sortOrder' => 15,
                        'class' => 'TestResolverNestedItemsHydrator'
                    ],
                    'model_hydrator' => [
                        'sortOrder' => 10,
                        'class' => 'TestResolverModelHydrator'
                    ],
                ]
            ]
        ];
    }

    public function testHydratorChainProvider()
    {
        $resolver = $this->getMockBuilder(StoreConfigResolver::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $testResolverData = [
            'id' => 2,
            'name' => 'test name',
            'model' => null
        ];

        $testModelHydrator = $this->getMockBuilder(HydratorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hydrate'])
            ->getMock();
        $testModelHydrator->expects($this->once())
            ->method('hydrate')
            ->willReturnCallback(function (&$resolverData) {
                unset($resolverData['model']);
                $do = new DataObject($resolverData);
                $resolverData['model'] = $do;
                $resolverData['sortOrderTest_field'] = 'some data';
            });
        $testNestedHydrator = $this->getMockBuilder(HydratorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['hydrate'])
            ->getMock();
        $testModelHydrator->expects($this->once())
            ->method('hydrate')
            ->willReturnCallback(function (&$resolverData) {
                $resolverData['model']->setData('nested_data', ['test_nested_data']);
                $resolverData['sortOrderTest_field'] = 'other data';
            });

        $this->objectManager->addSharedInstance($testModelHydrator, 'TestResolverModelHydrator');
        $this->objectManager->addSharedInstance($testNestedHydrator, 'TestResolverNestedItemsHydrator');

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

        //verify that hydrators were invoked in designated order
        $this->assertEquals('other data', $testResolverData['sortOrderTest_field']);

        // verify that hydrator instance is nt recreated
        $this->assertSame($hydrator, $this->provider->getHydratorForResolver($resolver));

        $this->objectManager->removeSharedInstance('TestResolverModelHydrator');
        $this->objectManager->removeSharedInstance('TestResolverNestedItemsHydrator');
    }

    public function testHydratorDoesNotExist()
    {
        $resolver = $this->getMockBuilder(ResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->assertNull($this->provider->getHydratorForResolver($resolver));
    }
}
