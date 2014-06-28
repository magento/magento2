<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Service\V1\Data;

use Magento\Framework\Service\V1\Data\SearchCriteria;

/**
 * Integration test for \Magento\Tax\Service\V1\Data\TaxRuleSearchResultsBuilder
 */
class TaxRuleSearchResultsBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManager
     */
    private $objectManager;

    /**
     * TaxRuleSearchResults builder
     *
     * @var TaxRuleSearchResultsBuilder
     */
    private $builder;

    /**
     * SearchCriteria builder
     *
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * TaxRule builder
     *
     * @var TaxRuleBuilder
     */
    private $taxRuleBuilder;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->builder = $this->objectManager->create('Magento\Tax\Service\V1\Data\TaxRuleSearchResultsBuilder');
        $this->searchCriteriaBuilder = $this->objectManager->create(
            'Magento\Framework\Service\V1\Data\SearchCriteriaBuilder'
        );
        $this->taxRuleBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\TaxRuleBuilder');
    }

    /**
     * @param array $dataArray
     * @dataProvider createDataProvider
     */
    public function testCreateWithPopulateWithArray($dataArray)
    {
        $taxRuleSearchResults = $this->builder->populateWithArray($dataArray)->create();
        $taxRuleSearchResults2 = $this->generateDataObjectWithSetters($dataArray);
        $this->assertInstanceOf('\Magento\Tax\Service\V1\Data\TaxRuleSearchResults', $taxRuleSearchResults);
        $this->assertInstanceOf('\Magento\Tax\Service\V1\Data\TaxRuleSearchResults', $taxRuleSearchResults2);
        $this->assertEquals($taxRuleSearchResults2, $taxRuleSearchResults);
        $this->assertEquals($dataArray, $taxRuleSearchResults->__toArray());
        $this->assertEquals($dataArray, $taxRuleSearchResults2->__toArray());
    }

    public function createDataProvider()
    {

        $ruleA = [
            TaxRule::ID => 1,
            TaxRule::CODE => 'code',
            TaxRule::CUSTOMER_TAX_CLASS_IDS => [1, 2],
            TaxRule::PRODUCT_TAX_CLASS_IDS => [3, 4],
            TaxRule::TAX_RATE_IDS => [1, 3],
            TaxRule::PRIORITY => 0,
            TaxRule::SORT_ORDER => 1,
        ];

        $ruleB = [
            TaxRule::ID => 1,
            TaxRule::TAX_RATE_IDS => [1],
        ];

        return [
            [[]],
            [
                [
                    TaxRuleSearchResults::KEY_SEARCH_CRITERIA => [
                        SearchCriteria::CURRENT_PAGE => 1,
                        SearchCriteria::PAGE_SIZE => 2,
                        SearchCriteria::SORT_ORDERS => SearchCriteria::SORT_DESC,
                        SearchCriteria::FILTER_GROUPS => [],
                    ],
                    TaxRuleSearchResults::KEY_TOTAL_COUNT => 2,
                    TaxRuleSearchResults::KEY_ITEMS => [
                        $ruleA,
                        $ruleB
                    ],
                ]
            ]
        ];
    }

    /**
     * @param array $dataArray
     * @param array $zipRangeArray
     * @dataProvider createDataProvider
     */
    public function testPopulate($dataArray)
    {
        $taxRuleSearchResults = $this->generateDataObjectWithSetters($dataArray);
        $taxRuleSearchResults2 = $this->builder->populate($taxRuleSearchResults)->create();
        $this->assertEquals($taxRuleSearchResults, $taxRuleSearchResults2);
    }

    public function testMergeDataObjects()
    {
        $ruleA = [
            TaxRule::ID => 1,
            TaxRule::CODE => 'code',
            TaxRule::CUSTOMER_TAX_CLASS_IDS => [1, 2],
            TaxRule::PRODUCT_TAX_CLASS_IDS => [3, 4],
            TaxRule::TAX_RATE_IDS => [1, 3],
            TaxRule::PRIORITY => 0,
            TaxRule::SORT_ORDER => 1,
        ];

        $ruleB = [
            TaxRule::ID => 1,
            TaxRule::TAX_RATE_IDS => [1],
        ];

        $data1 =                 [
            TaxRuleSearchResults::KEY_SEARCH_CRITERIA => [
                SearchCriteria::CURRENT_PAGE => 1,
                SearchCriteria::PAGE_SIZE => 2,
                SearchCriteria::SORT_ORDERS => SearchCriteria::SORT_DESC,
                SearchCriteria::FILTER_GROUPS => [],
            ],
            TaxRuleSearchResults::KEY_ITEMS => [
                $ruleA
            ],
        ];

        $data2 =                 [
            TaxRuleSearchResults::KEY_SEARCH_CRITERIA => [
                SearchCriteria::CURRENT_PAGE => 1,
                SearchCriteria::PAGE_SIZE => 2,
                SearchCriteria::SORT_ORDERS => SearchCriteria::SORT_DESC,
                SearchCriteria::FILTER_GROUPS => [],
            ],
            TaxRuleSearchResults::KEY_TOTAL_COUNT => 2,
            TaxRuleSearchResults::KEY_ITEMS => [
                $ruleB
            ],
        ];

        $dataMerged =                 [
            TaxRuleSearchResults::KEY_SEARCH_CRITERIA => [
                SearchCriteria::CURRENT_PAGE => 1,
                SearchCriteria::PAGE_SIZE => 2,
                SearchCriteria::SORT_ORDERS => SearchCriteria::SORT_DESC,
                SearchCriteria::FILTER_GROUPS => [],
            ],
            TaxRuleSearchResults::KEY_TOTAL_COUNT => 2,
            TaxRuleSearchResults::KEY_ITEMS => [
                $ruleB
            ],
        ];

        $taxRuleSearchResults = $this->builder->populateWithArray($dataMerged)->create();
        $taxRuleSearchResults1 = $this->builder->populateWithArray($data1)->create();
        $taxRuleSearchResults2 = $this->builder->populateWithArray($data2)->create();
        $taxRuleSearchResultsMerged = $this->builder->mergeDataObjects($taxRuleSearchResults1, $taxRuleSearchResults2);
        $this->assertEquals($taxRuleSearchResults->__toArray(), $taxRuleSearchResultsMerged->__toArray());
    }

    public function testMergeDataObjectWithArray()
    {
        $ruleA = [
            TaxRule::ID => 1,
            TaxRule::CODE => 'code',
            TaxRule::CUSTOMER_TAX_CLASS_IDS => [1, 2],
            TaxRule::PRODUCT_TAX_CLASS_IDS => [3, 4],
            TaxRule::TAX_RATE_IDS => [1, 3],
            TaxRule::PRIORITY => 0,
            TaxRule::SORT_ORDER => 1,
        ];

        $ruleB = [
            TaxRule::ID => 1,
            TaxRule::TAX_RATE_IDS => [1],
        ];

        $data1 =                 [
            TaxRuleSearchResults::KEY_SEARCH_CRITERIA => [
                SearchCriteria::CURRENT_PAGE => 1,
                SearchCriteria::PAGE_SIZE => 2,
                SearchCriteria::SORT_ORDERS => SearchCriteria::SORT_DESC,
                SearchCriteria::FILTER_GROUPS => [],
            ],
            TaxRuleSearchResults::KEY_ITEMS => [
                $ruleA
            ],
        ];

        $data2 =                 [
            TaxRuleSearchResults::KEY_SEARCH_CRITERIA => [
                SearchCriteria::CURRENT_PAGE => 1,
                SearchCriteria::PAGE_SIZE => 2,
                SearchCriteria::SORT_ORDERS => SearchCriteria::SORT_DESC,
                SearchCriteria::FILTER_GROUPS => [],
            ],
            TaxRuleSearchResults::KEY_TOTAL_COUNT => 1,
            TaxRuleSearchResults::KEY_ITEMS => [
                $ruleB
            ],
        ];

        $dataMerged =                 [
            TaxRuleSearchResults::KEY_SEARCH_CRITERIA => [
                SearchCriteria::CURRENT_PAGE => 1,
                SearchCriteria::PAGE_SIZE => 2,
                SearchCriteria::SORT_ORDERS => SearchCriteria::SORT_DESC,
                SearchCriteria::FILTER_GROUPS => [],
            ],
            TaxRuleSearchResults::KEY_TOTAL_COUNT => 1,
            TaxRuleSearchResults::KEY_ITEMS => [
                $ruleB
            ],
        ];

        $taxRuleSearchResults = $this->builder->populateWithArray($dataMerged)->create();
        $taxRuleSearchResults1 = $this->builder->populateWithArray($data1)->create();
        $taxRuleSearchResultsMerged = $this->builder->mergeDataObjectWithArray($taxRuleSearchResults1, $data2);
        $this->assertEquals($taxRuleSearchResults->__toArray(), $taxRuleSearchResultsMerged->__toArray());
    }

    /**
     * @param array $dataArray
     * @return TaxRuleSearchResults
     */
    protected function generateDataObjectWithSetters($dataArray)
    {
        $this->builder->populateWithArray([]);
        if (array_key_exists(TaxRuleSearchResults::KEY_ITEMS, $dataArray)) {
            $items = [];
            foreach ($dataArray[TaxRuleSearchResults::KEY_ITEMS] as $itemArray) {
                $items[] = $this->taxRuleBuilder->populateWithArray($itemArray)->create();
            }
            $this->builder->setItems($items);
        }
        if (array_key_exists(TaxRuleSearchResults::KEY_TOTAL_COUNT, $dataArray)) {
            $this->builder->setTotalCount($dataArray[TaxRuleSearchResults::KEY_TOTAL_COUNT]);
        }
        if (array_key_exists(TaxRuleSearchResults::KEY_SEARCH_CRITERIA, $dataArray)) {
            $this->builder->setSearchCriteria(
                $this->searchCriteriaBuilder->populateWithArray(
                    $dataArray[TaxRuleSearchResults::KEY_SEARCH_CRITERIA]
                )->create()
            );
        }
        return $this->builder->create();
    }
}
