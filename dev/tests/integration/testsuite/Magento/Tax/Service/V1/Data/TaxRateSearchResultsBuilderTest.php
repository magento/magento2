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
 * Integration test for \Magento\Tax\Service\V1\Data\TaxRateSearchResultsBuilder
 */
class TaxRateSearchResultsBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManager
     */
    private $objectManager;

    /**
     * TaxRateSearchResults builder
     *
     * @var TaxRateSearchResultsBuilder
     */
    private $taxRateSearchResultsBuilder;


    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->taxRateSearchResultsBuilder = $this->objectManager->create(
            'Magento\Tax\Service\V1\Data\TaxRateSearchResultsBuilder'
        );
    }

    /**
     * @param array $dataArray
     * @dataProvider createDataProvider
     */
    public function testCreateWithPopulateWithArray($dataArray)
    {
        $taxRateSearchResults = $this->taxRateSearchResultsBuilder->populateWithArray($dataArray)->create();
        $this->assertInstanceOf('\Magento\Tax\Service\V1\Data\TaxRateSearchResults', $taxRateSearchResults);
        $this->assertEquals($dataArray, $taxRateSearchResults->__toArray());
    }

    public function createDataProvider()
    {
        list($rateA, $rateB) = $this->getRateData();

        return [
            [[]],
            [
                [
                    TaxRateSearchResults::KEY_SEARCH_CRITERIA => [
                        SearchCriteria::CURRENT_PAGE => 1,
                        SearchCriteria::PAGE_SIZE => 2,
                        SearchCriteria::SORT_ORDERS => SearchCriteria::SORT_DESC,
                        SearchCriteria::FILTER_GROUPS => [],
                    ],
                    TaxRateSearchResults::KEY_TOTAL_COUNT => 2,
                    TaxRateSearchResults::KEY_ITEMS => [
                        $rateA,
                        $rateB
                    ],
                ]
            ]
        ];
    }

    /**
     * @param array $dataArray
     * @dataProvider createDataProvider
     */
    public function testPopulate($dataArray)
    {
        $taxRateSearchResultsFromArray = $this->taxRateSearchResultsBuilder->populateWithArray($dataArray)->create();
        $taxRateSearchResults = $this->taxRateSearchResultsBuilder->populate($taxRateSearchResultsFromArray)->create();
        $this->assertEquals($taxRateSearchResultsFromArray, $taxRateSearchResults);
    }

    /**
     * @dataProvider mergeDataProvider
     */
    public function testMergeDataObjects($firstDataSet, $secondDataSet, $mergedData)
    {
        $taxRateSearchResults = $this->taxRateSearchResultsBuilder->populateWithArray($mergedData)->create();
        $taxRateSearchResults1 = $this->taxRateSearchResultsBuilder->populateWithArray($firstDataSet)->create();
        $taxRateSearchResults2 = $this->taxRateSearchResultsBuilder->populateWithArray($secondDataSet)->create();
        $taxRateSearchResultsMerged = $this->taxRateSearchResultsBuilder->mergeDataObjects(
            $taxRateSearchResults1,
            $taxRateSearchResults2
        );
        $this->assertEquals($taxRateSearchResults->__toArray(), $taxRateSearchResultsMerged->__toArray());
    }

    /**
     * @dataProvider mergeDataProvider
     */
    public function testMergeDataObjectWithArray($firstDataSet, $secondDataSet, $mergedData)
    {
        $taxRateSearchResults = $this->taxRateSearchResultsBuilder->populateWithArray($mergedData)->create();
        $taxRateSearchResults1 = $this->taxRateSearchResultsBuilder->populateWithArray($firstDataSet)->create();
        $taxRateSearchResultsMerged = $this->taxRateSearchResultsBuilder->mergeDataObjectWithArray(
            $taxRateSearchResults1,
            $secondDataSet
        );
        $this->assertEquals($taxRateSearchResults->__toArray(), $taxRateSearchResultsMerged->__toArray());
    }

    public function mergeDataProvider()
    {
        list($rateA, $rateB) = $this->getRateData();

        return
            [
                'basicMerge' => [
                    'firstDataSet' => [
                        TaxRateSearchResults::KEY_SEARCH_CRITERIA => [
                            SearchCriteria::CURRENT_PAGE => 1,
                            SearchCriteria::PAGE_SIZE => 2,
                            SearchCriteria::SORT_ORDERS => SearchCriteria::SORT_DESC,
                            SearchCriteria::FILTER_GROUPS => [],
                        ],
                        TaxRateSearchResults::KEY_ITEMS => [
                            $rateA
                        ],
                    ],
                    'SecondDataSet' => [
                        TaxRateSearchResults::KEY_SEARCH_CRITERIA => [
                            SearchCriteria::CURRENT_PAGE => 1,
                            SearchCriteria::PAGE_SIZE => 2,
                            SearchCriteria::SORT_ORDERS => SearchCriteria::SORT_DESC,
                            SearchCriteria::FILTER_GROUPS => [],
                        ],
                        TaxRateSearchResults::KEY_TOTAL_COUNT => 2,
                        TaxRateSearchResults::KEY_ITEMS => [
                            $rateB
                        ],
                    ],
                    'mergedData' => [
                        TaxRateSearchResults::KEY_SEARCH_CRITERIA => [
                            SearchCriteria::CURRENT_PAGE => 1,
                            SearchCriteria::PAGE_SIZE => 2,
                            SearchCriteria::SORT_ORDERS => SearchCriteria::SORT_DESC,
                            SearchCriteria::FILTER_GROUPS => [],
                        ],
                        TaxRateSearchResults::KEY_TOTAL_COUNT => 2,
                        TaxRateSearchResults::KEY_ITEMS => [
                            $rateB
                        ],
                    ]
                ]
            ];
    }

    private function getRateData()
    {
        $rateA = [
            TaxRate::KEY_ID => 1,
            TaxRate::KEY_COUNTRY_ID => 'US',
            TaxRate::KEY_REGION_ID => '8',
            TaxRate::KEY_PERCENTAGE_RATE => '8.25',
            TaxRate::KEY_CODE => 'US-CA-*-Rate 1',
            TaxRate::KEY_POSTCODE => '78728'
        ];

        $rateB = [
            TaxRate::KEY_ID => 1,
            TaxRate::KEY_ZIP_RANGE => ['from' => 78701, 'to' => 78780],
        ];
        return ([$rateA, $rateB]);
    }
}
