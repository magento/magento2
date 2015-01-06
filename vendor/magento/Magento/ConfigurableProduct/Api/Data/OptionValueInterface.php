<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
