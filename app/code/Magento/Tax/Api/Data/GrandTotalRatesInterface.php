<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api\Data;

/**
 * Interface GrandTotalRatesInterface
 * @api
 * @since 2.0.0
 */
interface GrandTotalRatesInterface
{
    /**
     * Get tax percentage value
     *
     * @return string
     * @since 2.0.0
     */
    public function getPercent();

    /**
     * @param float $percent
     * @return $this
     * @since 2.0.0
     */
    public function setPercent($percent);

    /**
     * Tax rate title
     *
     * @return string
     * @since 2.0.0
     */
    public function getTitle();

    /**
     * @param string $title
     * @return $this
     * @since 2.0.0
     */
    public function setTitle($title);
}
