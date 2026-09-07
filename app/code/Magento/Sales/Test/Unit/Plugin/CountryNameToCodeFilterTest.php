<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Plugin;

use Magento\Framework\Locale\ListsInterface;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;
use Magento\Sales\Plugin\OrderGrid\CountryNameToCodeFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CountryNameToCodeFilterTest extends TestCase
{
    /** @var ListsInterface|MockObject */
    private $localeLists;

    /**
     * @var CountryNameToCodeFilter
     */
    private CountryNameToCodeFilter $plugin;

    /** @var Collection|MockObject */
    private $collection;

    protected function setUp(): void
    {
        $this->localeLists = $this->createMock(ListsInterface::class);
        $this->plugin = new CountryNameToCodeFilter($this->localeLists);
        $this->collection = $this->createMock(Collection::class);
    }

    public function testAroundAddFieldToFilterDoesNothingForNonAddressFields(): void
    {
        $this->localeLists->expects($this->never())->method('getOptionCountries');

        $proceed = function ($field, $condition) {
            return [$field, $condition];
        };

        $condition = ['like' => '%India%'];
        $result = $this->plugin->aroundAddFieldToFilter($this->collection, $proceed, 'customer_email', $condition);

        $this->assertSame(['customer_email', $condition], $result);
    }

    #[DataProvider('addressFilterDataProvider')]
    public function testAroundAddFieldToFilterConvertsCountryNameToIso2CodeInAddressFilters(
        string $field,
        array $condition,
        array $expectedCondition,
        bool $expectsCountryList
    ): void {
        if ($expectsCountryList) {
            $this->localeLists->expects($this->once())
                ->method('getOptionCountries')
                ->willReturn([
                    ['value' => 'IN', 'label' => 'India'],
                    ['value' => 'US', 'label' => 'United States'],
                ]);
        } else {
            $this->localeLists->expects($this->never())->method('getOptionCountries');
        }

        $proceed = function ($passedField, $passedCondition) {
            return [$passedField, $passedCondition];
        };

        $result = $this->plugin->aroundAddFieldToFilter($this->collection, $proceed, $field, $condition);

        $this->assertSame($field, $result[0]);
        $this->assertSame($expectedCondition, $result[1]);
    }

    /**
     * @return array[]
     */
    public static function addressFilterDataProvider(): array
    {
        return [
            'shipping like %India% -> %IN%' => [
                'shipping_address',
                ['like' => '%India%'],
                ['like' => '%IN%'],
                true,
            ],
            'billing like address ending India -> ending IN' => [
                'billing_address',
                ['like' => '%c1,street1,noida1, India%'],
                ['like' => '%c1,street1,noida1, IN%'],
                true,
            ],
            'already iso2 stays uppercased' => [
                'shipping_address',
                ['like' => '%in%'],
                ['like' => '%IN%'],
                false,
            ],
            'unknown country name unchanged' => [
                'shipping_address',
                ['like' => '%Neverland%'],
                ['like' => '%Neverland%'],
                true, // it will consult the country list, but won't find a match
            ],
            'non-string condition value unchanged' => [
                'shipping_address',
                ['eq' => null],
                ['eq' => null],
                false,
            ],
        ];
    }
}
