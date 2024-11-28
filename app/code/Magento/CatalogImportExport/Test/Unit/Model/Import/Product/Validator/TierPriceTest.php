<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\StoreResolver;
use Magento\CatalogImportExport\Model\Import\Product\Validator\TierPrice;
use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Data\Group;
use Magento\Customer\Model\ResourceModel\GroupRepository;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TierPriceTest extends TestCase
{
    /** @var TierPrice */
    protected $tierPrice;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var GroupRepositoryInterface|MockObject */
    protected $groupRepositoryInterface;

    /** @var SearchCriteriaBuilder|MockObject */
    protected $searchCriteriaBuilder;

    /** @var StoreResolver|MockObject */
    protected $storeResolver;

    protected function setUp(): void
    {
        $this->groupRepositoryInterface = $this->createMock(
            GroupRepository::class
        );
        $searchCriteriaSearch = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $this->searchCriteriaBuilder->expects($this->any())->method('create')
            ->willReturn($searchCriteriaSearch);
        $this->storeResolver = $this->createMock(
            StoreResolver::class
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->tierPrice = $this->objectManagerHelper->getObject(
            TierPrice::class,
            [
                'groupRepository' => $this->groupRepositoryInterface,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'storeResolver' => $this->storeResolver
            ]
        );
    }

    /**
     * @param $groupId
     * @return TierPrice
     */
    protected function processInit($groupId)
    {
        $searchResult = $this->getMockForAbstractClass(GroupSearchResultsInterface::class);
        $this->groupRepositoryInterface->expects($this->once())->method('getList')->willReturn($searchResult);
        $group = $this->createMock(Group::class);
        $group->expects($this->once())->method('getId')->willReturn($groupId);
        $searchResult->expects($this->once())->method('getItems')->willReturn([$group]);
        return $this->tierPrice->init(null);
    }

    public function testInit()
    {
        $result = $this->processInit(3);
        $this->assertEquals($this->tierPrice, $result);
    }

    /**
     * @param array $data
     * @param int $groupId
     * @param array|null $website
     * @param array $expected
     * @dataProvider tierPriceDataProvider
     */
    public function testIsValid($data, $groupId, $website, $expected)
    {
        $this->processInit($groupId);
        if ($website) {
            $this->storeResolver
                ->expects($this->any())
                ->method('getWebsiteCodeToId')
                ->with($website['id'])
                ->willReturn($website['code']);
        }
        $result = $this->tierPrice->isValid($data);
        $this->assertEquals($expected['result'], $result);
        $messages = $this->tierPrice->getMessages();
        $this->assertEquals($expected['messages'], $messages);
    }

    /**
     * @return array
     */
    public static function tierPriceDataProvider()
    {
        return [
            'empty' => [
                [],
                1,
                ['id' => 0, 'code' => ''],
                ['result' => true, 'messages' => []],
            ],
            'valid1' => [
                [
                    '_tier_price_website' => 'all',
                    '_tier_price_customer_group' => '1',
                    '_tier_price_qty' => '1',
                    '_tier_price_price' => '1'
                ],
                1,
                null,
                ['result' => true, 'messages' => []],
            ],
            'invalidPriceWebsite' => [
                [
                    '_tier_price_website' => '1',
                    '_tier_price_customer_group' => '1',
                    '_tier_price_qty' => '1',
                    '_tier_price_price' => '1'
                ],
                1,
                null,
                ['result' => false, 'messages' => [ 0 => 'tierPriceWebsiteInvalid']],
            ],
            'invalidIncomplete1' => [
                [
                    '_tier_price_qty' => '1'
                ],
                1,
                null,
                ['result' => false, 'messages' => [ 0 => 'tierPriceDataIsIncomplete']],
            ],
            'invalidIncomplete2' => [
                [
                    '_tier_price_customer_group' => '1'
                ],
                1,
                null,
                ['result' => false, 'messages' => [ 0 => 'tierPriceDataIsIncomplete']],
            ],
            'invalidIncomplete3' => [
                [
                    '_tier_price_price' => '1'
                ],
                1,
                null,
                ['result' => false, 'messages' => [ 0 => 'tierPriceDataIsIncomplete']],
            ],
            'invalidSite' => [
                [
                    '_tier_price_website' => '1',
                    '_tier_price_customer_group' => 'all',
                    '_tier_price_qty' => '1',
                    '_tier_price_price' => '1'
                ],
                1,
                null,
                ['result' => false, 'messages' => [ 0 => 'tierPriceWebsiteInvalid']],
            ],
            'invalidGroup' => [
                [
                    '_tier_price_website' => 'all',
                    '_tier_price_customer_group' => '1',
                    '_tier_price_qty' => '1',
                    '_tier_price_price' => '1'
                ],
                2,
                null,
                ['result' => false, 'messages' => [ 0 => 'tierPriceGroupInvalid']],
            ],
            'invalidQty' => [
                [
                    '_tier_price_website' => 'all',
                    '_tier_price_customer_group' => '1',
                    '_tier_price_qty' => '-1',
                    '_tier_price_price' => '-1'
                ],
                1,
                null,
                ['result' => false, 'messages' => [ 0 => 'invalidTierPriceOrQty']],
            ],
        ];
    }
}
