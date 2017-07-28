<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

/**
 * Class SearchTermManager
 *
 * Class responsible for applying search terms to description
 * based on search terms description
 * @since 2.2.0
 */
class SearchTermManager
{
    /**
     * @var array
     * @since 2.2.0
     */
    private $searchTerms;

    /**
     * @var array
     * @since 2.2.0
     */
    private $searchTermsUseRate;

    /**
     * @var int
     * @since 2.2.0
     */
    private $totalProductsCount;

    /**
     * @param array $searchTerms
     * @param int $totalProductsCount
     * @since 2.2.0
     */
    public function __construct(array $searchTerms, $totalProductsCount)
    {
        $this->searchTerms = $searchTerms;
        $this->totalProductsCount = (int) $totalProductsCount;
    }

    /**
     * Apply search terms to product description
     * based on search terms use distribution
     *
     * @param string $description
     * @param int $currentProductIndex
     * @return void
     * @since 2.2.0
     */
    public function applySearchTermsToDescription(&$description, $currentProductIndex)
    {
        if ($this->searchTermsUseRate === null) {
            $this->calculateSearchTermsUseRate();
        }

        foreach ($this->searchTerms as &$searchTerm) {
            if ($this->searchTermsUseRate[$searchTerm['term']]['use_rate'] > 0
                && $currentProductIndex % $this->searchTermsUseRate[$searchTerm['term']]['use_rate'] === 0
                && $this->searchTermsUseRate[$searchTerm['term']]['used'] < $searchTerm['count']
            ) {
                $description .= ' ' . $searchTerm['term'];
                $this->searchTermsUseRate[$searchTerm['term']]['used'] += 1;
            }
        }
    }

    /**
     * Calculates search terms use distribution
     * based on total amount of products that will be generated
     * and number of each search term
     *
     * @return void;
     * @since 2.2.0
     */
    private function calculateSearchTermsUseRate()
    {
        foreach ($this->searchTerms as $searchTerm) {
            $this->searchTermsUseRate[$searchTerm['term']] = [
                'use_rate' => floor($this->totalProductsCount / $searchTerm['count']),
                'used' => 0
            ];
        }
    }
}
