<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Model;


class UrlFinderPool
{
    private const SORT_KEY = 'sortOrder';

    /**
     * @var array
     */
    private $urlFinders;

    /**
     * @param array $urlFinders
     */
    public function __construct(array $urlFinders)
    {
        foreach($urlFinders as $urlFinder) {
            if( ! $urlFinder['object'] instanceof UrlFinderInterface) {
                throw new \InvalidArgumentException('Must be instance of ' . UrlFinderInterface::class);
            }
        }
        $this->urlFinders = $urlFinders;
        uasort($this->urlFinders, [$this, 'compareSortOrder']);
    }

    /**
     * Get list of UrlFinders
     *
     * @return array
     */
    public function getUrlFinders() : array
    {
        return array_map(function($u){return $u['object'];}, $this->urlFinders);
    }

    /**
     * Compare sort order for two items
     *
     * @param array $first
     * @param array $second
     * @return int
     */
    private function compareSortOrder(array $first, array $second) : int
    {
        return (int)$first[self::SORT_KEY] <=> (int)$second[self::SORT_KEY];
    }
}