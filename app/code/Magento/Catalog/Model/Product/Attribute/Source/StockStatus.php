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
 * @api
 * @since 100.0.2
 */
class StockStatus extends AbstractSource implements SourceInterface, OptionSourceInterface
{
    /**
     * Product stock status values
     */
    const IN_STOCK = 1;

    const OUT_OF_STOCK = 0;

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public static function getOptionArray() : array
    {
        return [self::IN_STOCK=> __('In Stock'), self::OUT_OF_STOCK => __('Out of Stock')];
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions() : array
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param int $optionId
     * @return string
     */
    public function getOptionText(int $optionId) : string
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }

}