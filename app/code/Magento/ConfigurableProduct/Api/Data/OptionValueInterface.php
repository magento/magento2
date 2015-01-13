<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Api\Data;

interface OptionValueInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * @return float|null
     */
    public function getPricingValue();

    /**
     * @return int|null
     */
    public function getIsPercent();

    /**
     * @return int
     */
    public function getValueIndex();
}
