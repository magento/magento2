<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class OptionValue extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\ConfigurableProduct\Api\Data\OptionValueInterface
{
    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getPricingValue()
    {
        return $this->getData('pricing_value');
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getIsPercent()
    {
        return $this->getData('is_percent');
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function getValueIndex()
    {
        return $this->getData('value_index');
    }
}
