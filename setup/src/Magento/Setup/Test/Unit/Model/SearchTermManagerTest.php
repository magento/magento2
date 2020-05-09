<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\SearchTermManager;
use PHPUnit\Framework\TestCase;

class SearchTermManagerTest extends TestCase
{
    /**
     * @var SearchTermManager
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

    protected function setUp(): void
    {
        $this->searchTermManager = new SearchTermManager(
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
