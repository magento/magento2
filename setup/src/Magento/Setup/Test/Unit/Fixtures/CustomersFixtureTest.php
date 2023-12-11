<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Fixtures\CustomersFixture;
use Magento\Setup\Fixtures\FixtureModel;
use Magento\Setup\Model\Customer\CustomerDataGenerator;
use Magento\Setup\Model\Customer\CustomerDataGeneratorFactory;
use Magento\Setup\Model\FixtureGenerator\CustomerGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomersFixtureTest extends TestCase
{
    /**
     * @var MockObject|FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var MockObject|CustomerGenerator
     */
    private $customerGeneratorMock;

    /**
     * @var MockObject|CustomerDataGeneratorFactory
     */
    private $customerDataGeneratorFactoryMock;

    /**
     * @var CustomersFixture
     */
    private $model;

    /**
     * @var MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var MockObject
     */
    private $collectionMock;

    protected function setUp(): void
    {
        $this->fixtureModelMock = $this->createMock(FixtureModel::class);

        $this->customerGeneratorMock =
            $this->createMock(CustomerGenerator::class);

        $this->customerDataGeneratorFactoryMock =
            $this->createMock(CustomerDataGeneratorFactory::class);

        $this->collectionFactoryMock =
            $this->createPartialMock(
                \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory::class,
                ['create']
            );

        $this->collectionMock = $this->createMock(Collection::class);

        $this->model = (new ObjectManager($this))->getObject(CustomersFixture::class, [
            'fixtureModel' => $this->fixtureModelMock,
            'customerGenerator' => $this->customerGeneratorMock,
            'customerDataGeneratorFactory' => $this->customerDataGeneratorFactoryMock,
            'collectionFactory' => $this->collectionFactoryMock
        ]);
    }

    public function testExecute()
    {
        $entitiesInDB = 20;
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())->method('getSize')->willReturn($entitiesInDB);

        $customersNumber = 100500;
        $customerConfig = [
            'some-key' => 'some value'
        ];

        $this->fixtureModelMock
            ->expects($this->exactly(2))
            ->method('getValue')
            ->will($this->onConsecutiveCalls($customersNumber, $customerConfig));

        $customerDataGeneratorMock = $this->createMock(CustomerDataGenerator::class);

        $this->customerDataGeneratorFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($customerConfig)
            ->willReturn($customerDataGeneratorMock);

        $this->customerGeneratorMock
            ->expects($this->once())
            ->method('generate')
            ->with(
                $customersNumber - $entitiesInDB,
                $this->arrayHasKey('customer_data')
            );

        $this->model->execute();
    }

    public function testDoNoExecuteIfCustomersAlreadyGenerated()
    {
        $this->collectionFactoryMock->expects($this->once())->method('create')->willReturn($this->collectionMock);
        $this->collectionMock->expects($this->once())->method('getSize')->willReturn(20);
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(20);
        $this->customerDataGeneratorFactoryMock->expects($this->never())->method('create');

        $this->model->execute();
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Generating customers', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame(
            [
                'customers' => 'Customers'
            ],
            $this->model->introduceParamLabels()
        );
    }
}
