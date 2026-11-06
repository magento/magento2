<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Test\Unit\CacheIdFactorProviders;

use Magento\Customer\Model\Group;
use Magento\Customer\Model\GroupRegistry;
use Magento\Customer\Model\ResourceModel\GroupRepository;
use Magento\CustomerGraphQl\CacheIdFactorProviders\CustomerTaxRateProvider;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Tax\Model\Calculation as CalculationModel;
use Magento\Tax\Model\ResourceModel\Calculation as CalculationResource;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerTaxRateProviderTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var GroupRepository|MockObject
     */
    private $groupRepository;

    /**
     * @var GroupRegistry|MockObject
     */
    private $groupRegistry;

    /**
     * @var CalculationModel|MockObject
     */
    private $calculationModel;

    /**
     * @var CalculationResource|MockObject
     */
    private $calculationResource;

    /**
     * @var CustomerTaxRateProvider
     */
    private $provider;

    protected function setUp(): void
    {
        $this->groupRepository = $this->createMock(GroupRepository::class);
        $this->groupRegistry = $this->createMock(GroupRegistry::class);
        $this->calculationModel = $this->createMock(CalculationModel::class);
        $this->calculationResource = $this->createMock(CalculationResource::class);
        $this->provider = new CustomerTaxRateProvider(
            $this->groupRepository,
            $this->calculationModel,
            $this->calculationResource,
            $this->groupRegistry
        );
    }

    public function testFactorValueUsesTaxClassOfGroupWithoutLoadingGroupDataObject(): void
    {
        $store = $this->createStub(StoreInterface::class);
        $context = $this->createContext(false, null, 0, $store);

        $group = $this->createPartialMockWithReflection(Group::class, ['getTaxClassId']);
        $group->expects($this->once())->method('getTaxClassId')->willReturn('3');
        $this->groupRegistry->expects($this->once())->method('retrieve')->with(0)->willReturn($group);
        $this->groupRepository->expects($this->never())->method('getById');

        $rateRequest = new DataObject();
        $this->calculationModel->expects($this->once())
            ->method('getRateRequest')
            ->with(null, null, '3', $store, 0)
            ->willReturn($rateRequest);
        $this->calculationResource->expects($this->once())
            ->method('getRateInfo')
            ->with($rateRequest)
            ->willReturn(['value' => 8.25]);

        $this->assertSame('8.25', $this->provider->getFactorValue($context));
    }

    public function testFactorValueUsesCustomerGroupAndIdOfLoggedInCustomer(): void
    {
        $store = $this->createStub(StoreInterface::class);
        $context = $this->createContext(true, 42, 2, $store);

        $group = $this->createPartialMockWithReflection(Group::class, ['getTaxClassId']);
        $group->expects($this->once())->method('getTaxClassId')->willReturn('5');
        $this->groupRegistry->expects($this->once())->method('retrieve')->with(2)->willReturn($group);
        $this->groupRepository->expects($this->never())->method('getById');

        $rateRequest = new DataObject();
        $this->calculationModel->expects($this->once())
            ->method('getRateRequest')
            ->with(null, null, '5', $store, 42)
            ->willReturn($rateRequest);
        $this->calculationResource->expects($this->once())->method('getRateInfo')->willReturn(['value' => 0]);

        $this->assertSame('0', $this->provider->getFactorValue($context));
    }

    /**
     * @param bool $isCustomer
     * @param int|null $userId
     * @param int $groupId
     * @param StoreInterface $store
     * @return ContextInterface
     */
    private function createContext(
        bool $isCustomer,
        ?int $userId,
        int $groupId,
        StoreInterface $store
    ): ContextInterface {
        $extension = $this->createPartialMockWithReflection(
            ContextExtensionInterface::class,
            ['getIsCustomer', 'getCustomerGroupId', 'getStore']
        );
        $extension->expects($this->once())->method('getIsCustomer')->willReturn($isCustomer);
        $extension->expects($this->once())->method('getCustomerGroupId')->willReturn($groupId);
        $extension->expects($this->once())->method('getStore')->willReturn($store);

        $context = $this->createStub(ContextInterface::class);
        $context->method('getExtensionAttributes')->willReturn($extension);
        $context->method('getUserId')->willReturn($userId);

        return $context;
    }
}
