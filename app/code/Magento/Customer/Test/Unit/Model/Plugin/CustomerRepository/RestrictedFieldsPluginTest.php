<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Plugin\CustomerRepository;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Plugin\CustomerRepository\RestrictedFieldsPlugin;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\InputException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Forbidden customer fields
 */
class RestrictedFieldsPluginTest extends TestCase
{
    /**
     * @var RestrictedFieldsPlugin
     */
    private RestrictedFieldsPlugin $plugin;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private CustomerRepositoryInterface|MockObject $repositoryMock;

    /**
     * @var SearchCriteriaInterface|MockObject
     */
    private SearchCriteriaInterface|MockObject $searchCriteriaMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->plugin = new RestrictedFieldsPlugin(['password_hash', 'rp_token', 'confirmation']);
        $this->repositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);
    }

    /**
     * Verifies that filtering on a restricted field throws an InputException.
     */
    #[DataProvider('restrictedFieldsProvider')]
    public function testBeforeGetListThrowsExceptionForRestrictedField(string $field): void
    {
        $filterMock = $this->buildFilterMock($field);
        $filterGroupMock = $this->buildFilterGroupMock([$filterMock]);

        $this->searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupMock]);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage((string) __('The search criteria filter field is invalid.'));

        $this->plugin->beforeGetList($this->repositoryMock, $this->searchCriteriaMock);
    }

    /**
     * Verifies that filtering on an allowed field passes through and returns the search criteria unchanged.
     */
    public function testBeforeGetListPassesThroughForAllowedField(): void
    {
        $filterMock = $this->buildFilterMock('email');
        $filterGroupMock = $this->buildFilterGroupMock([$filterMock]);

        $this->searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupMock]);

        $result = $this->plugin->beforeGetList($this->repositoryMock, $this->searchCriteriaMock);

        $this->assertSame([$this->searchCriteriaMock], $result);
    }

    /**
     * Verifies that a search criteria with no filter groups passes through unchanged.
     */
    public function testBeforeGetListPassesThroughForNoFilters(): void
    {
        $this->searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([]);

        $result = $this->plugin->beforeGetList($this->repositoryMock, $this->searchCriteriaMock);

        $this->assertSame([$this->searchCriteriaMock], $result);
    }

    /**
     * Verifies that sorting by a restricted field throws an InputException.
     */
    #[DataProvider('restrictedFieldsProvider')]
    public function testBeforeGetListThrowsExceptionForRestrictedSortField(string $field): void
    {
        $sortOrderMock = $this->buildSortOrderMock($field);

        $this->searchCriteriaMock->method('getFilterGroups')->willReturn([]);
        $this->searchCriteriaMock->expects($this->once())
            ->method('getSortOrders')
            ->willReturn([$sortOrderMock]);

        $this->expectException(InputException::class);
        $this->expectExceptionMessage((string) __('The search criteria sort field is invalid.'));

        $this->plugin->beforeGetList($this->repositoryMock, $this->searchCriteriaMock);
    }

    /**
     * Verifies that sorting by an allowed field passes through and returns the search criteria unchanged.
     */
    public function testBeforeGetListPassesThroughForAllowedSortField(): void
    {
        $sortOrderMock = $this->buildSortOrderMock('email');

        $this->searchCriteriaMock->method('getFilterGroups')->willReturn([]);
        $this->searchCriteriaMock->expects($this->once())
            ->method('getSortOrders')
            ->willReturn([$sortOrderMock]);

        $result = $this->plugin->beforeGetList($this->repositoryMock, $this->searchCriteriaMock);

        $this->assertSame([$this->searchCriteriaMock], $result);
    }

    /**
     * Verifies that a search criteria with no sort orders (null, per the interface contract) passes through.
     */
    public function testBeforeGetListPassesThroughForNullSortOrders(): void
    {
        $this->searchCriteriaMock->method('getFilterGroups')->willReturn([]);
        $this->searchCriteriaMock->expects($this->once())
            ->method('getSortOrders')
            ->willReturn(null);

        $result = $this->plugin->beforeGetList($this->repositoryMock, $this->searchCriteriaMock);

        $this->assertSame([$this->searchCriteriaMock], $result);
    }

    /**
     * Verifies that a filter with no field set (getField() returns null) does not throw and passes through.
     */
    public function testBeforeGetListPassesThroughForNullFilterField(): void
    {
        $filterMock = $this->buildFilterMock(null);
        $filterGroupMock = $this->buildFilterGroupMock([$filterMock]);

        $this->searchCriteriaMock->expects($this->once())
            ->method('getFilterGroups')
            ->willReturn([$filterGroupMock]);

        $result = $this->plugin->beforeGetList($this->repositoryMock, $this->searchCriteriaMock);

        $this->assertSame([$this->searchCriteriaMock], $result);
    }

    /**
     * Verifies that a sort order with no field set (getField() returns null) does not throw and passes through.
     */
    public function testBeforeGetListPassesThroughForNullSortOrderField(): void
    {
        $sortOrderMock = $this->buildSortOrderMock(null);

        $this->searchCriteriaMock->method('getFilterGroups')->willReturn([]);
        $this->searchCriteriaMock->expects($this->once())
            ->method('getSortOrders')
            ->willReturn([$sortOrderMock]);

        $result = $this->plugin->beforeGetList($this->repositoryMock, $this->searchCriteriaMock);

        $this->assertSame([$this->searchCriteriaMock], $result);
    }

    /**
     * list of customer fields, including case variants that must be blocked regardless of casing.
     *
     * @return array
     */
    public static function restrictedFieldsProvider(): array
    {
        return [
            'password_hash' => ['password_hash'],
            'rp_token'      => ['rp_token'],
            'confirmation'  => ['confirmation'],
            'Password_Hash (mixed case)' => ['Password_Hash'],
            'RP_TOKEN (upper case)'      => ['RP_TOKEN'],
            'Confirmation (title case)'  => ['Confirmation'],
        ];
    }

    /**
     * Builds a Filter mock that returns the given field name.
     *
     * @param string|null $field
     * @return Filter|MockObject
     */
    private function buildFilterMock(?string $field): Filter|MockObject
    {
        $filterMock = $this->createMock(Filter::class);
        $filterMock->method('getField')->willReturn($field);
        return $filterMock;
    }

    /**
     * Builds a FilterGroup mock containing the given filters.
     *
     * @param array $filters
     * @return FilterGroup|MockObject
     */
    private function buildFilterGroupMock(array $filters): FilterGroup|MockObject
    {
        $filterGroupMock = $this->createMock(FilterGroup::class);
        $filterGroupMock->method('getFilters')->willReturn($filters);
        return $filterGroupMock;
    }

    /**
     * Builds a SortOrder mock that returns the given field name.
     *
     * @param string|null $field
     * @return SortOrder|MockObject
     */
    private function buildSortOrderMock(?string $field): SortOrder|MockObject
    {
        $sortOrderMock = $this->createMock(SortOrder::class);
        $sortOrderMock->method('getField')->willReturn($field);
        return $sortOrderMock;
    }
}
