<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Validator;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class TierPriceTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\CatalogImportExport\Model\Import\Product\Validator\TierPrice */
    protected $tierPrice;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Customer\Api\GroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $groupRepositoryInterface;

    /** @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject */
    protected $searchCriteriaBuilder;

    /** @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeResolver;

    protected function setUp()
    {
        $this->groupRepositoryInterface = $this->createMock(
            \Magento\Customer\Model\ResourceModel\GroupRepository::class
        );
        $this->searchCriteriaSearch = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->searchCriteriaBuilder = $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->searchCriteriaBuilder->expects($this->any())->method('create')->willReturn($this->searchCriteriaSearch);
        $this->storeResolver = $this->createMock(
            \Magento\CatalogImportExport\Model\Import\Product\StoreResolver::class
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->tierPrice = $this->objectManagerHelper->getObject(
            \Magento\CatalogImportExport\Model\Import\Product\Validator\TierPrice::class,
            [
                'groupRepository' => $this->groupRepositoryInterface,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'storeResolver' => $this->storeResolver
            ]
        );
    }

    protected function processInit($groupId)
    {
        $searchResult = $this->createMock(\Magento\Customer\Api\Data\GroupSearchResultsInterface::class);
        $this->groupRepositoryInterface->expects($this->once())->method('getList')->willReturn($searchResult);
        $group = $this->createMock(\Magento\Customer\Model\Data\Group::class);
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
    public function tierPriceDataProvider()
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
