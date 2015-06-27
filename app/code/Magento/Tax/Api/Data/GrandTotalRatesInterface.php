<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;


interface GrandTotalRatesInterface extends \Magento\Framework\Api\ExtensibleDataInterface
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

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Tax\Api\Data\GrandTotalRatesExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Tax\Api\Data\GrandTotalRatesExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Tax\Api\Data\GrandTotalRatesExtensionInterface $extensionAttributes
    );
}
