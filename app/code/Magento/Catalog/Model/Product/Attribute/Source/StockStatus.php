<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Product stock status functionality model
 *
 */
class StockStatus extends AbstractSource implements SourceInterface, OptionSourceInterface
{
    /**
     * Stock status value for product in stock
     */
    private const IN_STOCK = 1;

    /**
     * Stock status value for out of stock product
     */
    private const OUT_OF_STOCK = 0;

    /**
     * Retrieve option array
     *
     * @return array
     */
    private function getOptionArray() : array
    {
        return [self::IN_STOCK=> __('In Stock'), self::OUT_OF_STOCK => __('Out of Stock')];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return array
     */
    public function getAllOptions() : array
    {
        $result = [];

        foreach ($this->getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param int|string $optionId
     * @return string|bool
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
