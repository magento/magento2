<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api\Data;

/**
 * Interface GrandTotalRatesInterface
 * @api
 * @since 100.0.2
 */
interface GrandTotalRatesInterface
{
    /**
     * Get tax percentage value
     *
     * @return string
     */
    public function getPercent();

    /**
     * @param float $percent
     * @return $this
     */
    public function setPercent($percent);

    /**
     * Tax rate title
     *
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title);
}
