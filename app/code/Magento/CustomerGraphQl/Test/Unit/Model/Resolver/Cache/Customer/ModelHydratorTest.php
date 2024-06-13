<?php
/**
 * Copyright 2024 Adobe.
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Test\Unit\Model\Resolver\Cache\Customer;

use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Data\CustomerFactory;
use Magento\CustomerGraphQl\Model\Resolver\Cache\Customer\ModelHydrator;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\EntityManager\HydratorPool;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ModelHydratorTest extends TestCase
{
    /**
     * @var CustomerFactory|MockObject
     */
    private $customerFactory;

    /**
     * @var HydratorPool|MockObject
     */
    private $hydratorPool;

    /**
     * @var HydratorInterface|MockObject
     */
    private $hydrator;

    /**
     * @var ModelHydrator
     */
    private $modelHydrator;

    protected function setUp(): void
    {
        $this->customerFactory = $this->createMock(CustomerFactory::class);
        $this->hydratorPool = $this->createMock(HydratorPool::class);
        $this->hydrator = $this->createMock(HydratorInterface::class);

        $this->modelHydrator = new ModelHydrator(
            $this->customerFactory,
            $this->hydratorPool
        );
    }

    /**
     * Test hydrate method with existing model
     *
     * @return void
     * @throws Exception
     */

    public function testHydrateWithExistingModel()
    {
        $customer = $this->createMock(Customer::class);
        $resolverData = [
            'model_id' => 1,
            'model_entity_type' => 'customer',
            'model_data' => ['id' => 1]
        ];

        $this->customerFactory
            ->method('create')
            ->willReturn($customer);
        $this->hydrator
            ->method('hydrate')
            ->with($customer, $resolverData['model_data'])
            ->willReturnSelf();
        $this->hydratorPool
            ->method('getHydrator')
            ->with('customer')
            ->willReturn($this->hydrator);

        $this->modelHydrator->hydrate($resolverData);
        $this->modelHydrator->hydrate($resolverData);
        $this->assertSame($customer, $resolverData['model']);
    }

    /**
     * Test hydrate method with new model
     *
     * @return void
     * @throws Exception
     */

    public function testHydrateWithNewModel()
    {
        $customer = $this->createMock(Customer::class);
        $resolverData = [
            'model_id' => 1,
            'model_entity_type' => 'customer',
            'model_data' => ['id' => 1]
        ];

        $this->customerFactory
            ->method('create')
            ->willReturn($customer);
        $this->hydratorPool
            ->method('getHydrator')
            ->willReturn($this->hydrator);
        $this->hydrator->expects($this->once())
            ->method('hydrate')
            ->with($customer, $resolverData['model_data']);

        $this->modelHydrator->hydrate($resolverData);
        $this->assertSame($customer, $resolverData['model']);
    }

    /**
     * Test that resetState method resets the state of the modelHydrator
     *
     * @return void
     * @throws Exception
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function testResetState()
    {
        $customer1 = $this->createMock(Customer::class);
        $customer2 = $this->createMock(Customer::class);

        $resolverData1 = [
            'model_id' => 1,
            'model_entity_type' => 'customer',
            'model_data' => ['id' => 1]
        ];

        $resolverData2 = [
            'model_id' => 2,
            'model_entity_type' => 'customer',
            'model_data' => ['id' => 2]
        ];

        $this->customerFactory
            ->method('create')
            ->willReturnOnConsecutiveCalls($customer1, $customer2);
        $this->hydratorPool
            ->method('getHydrator')
            ->willReturn($this->hydrator);

        $matcher = $this->exactly(2);
        $expected1 = $resolverData1['model_data'];
        $expected2 = $resolverData2['model_data'];

        $this->hydrator
            ->expects($matcher)
            ->method('hydrate')
            ->willReturnCallback(function ($model, $data) use ($matcher, $expected1, $expected2) {
                match ($matcher->numberOfInvocations()) {
                    1 => $this->assertEquals($expected1, $data),
                    2 => $this->assertEquals($expected2, $data),
                };
            });

        $this->modelHydrator->hydrate($resolverData1);

        $this->assertArrayHasKey('model', $resolverData1);
        $this->assertEquals(1, $resolverData1['model_id']);

        $this->modelHydrator->_resetState();

        $this->modelHydrator->hydrate($resolverData2);

        $this->assertArrayHasKey('model', $resolverData2);
        $this->assertEquals(2, $resolverData2['model_id']);
    }
}
