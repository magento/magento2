<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

class SearchTermManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Model\SearchTermManager
     */
    private $searchTermManager;

    /**
     * @var int
     */
    private $totalProductsCount = 150;

    /**
     * @var array
     */
    private $searchTermConfiguration = [
        [
            'term' => 'x-wing',
            'count' => '33'
        ], [
            'term' => 'tie-fighter',
            'count' => '100'
        ], [
            'term' => 'n-1 starfighter',
            'count' => '42'
        ],
    ];

    /**
     * @var array
     */
    private $searchTermsUsage = [
        'x-wing' => [
            'used' => 0
        ],
        'tie-fighter' => [
            'used' => 0
        ],
        'n-1 starfighter' => [
            'used' => 0
        ]
    ];

    public function setUp()
    {
        $this->searchTermManager = new \Magento\Setup\Model\SearchTermManager(
            $this->searchTermConfiguration,
            $this->totalProductsCount
        );
    }

    public function testSearchTermApplied()
    {
        for ($productIndex=1; $productIndex<=$this->totalProductsCount; $productIndex++) {
            $description = 'Fleet: ';
            $this->searchTermManager->applySearchTermsToDescription($description, $productIndex);

            foreach (array_keys($this->searchTermsUsage) as $searchTerm) {
                if (preg_match("/\\b$searchTerm\\b/", $description)) {
                    $this->searchTermsUsage[$searchTerm]['used']++;
                }
            }
        }

        foreach ($this->searchTermConfiguration as $searchTerm) {
            $this->assertEquals($searchTerm['count'], $this->searchTermsUsage[$searchTerm['term']]['used']);
        }
    }
}
