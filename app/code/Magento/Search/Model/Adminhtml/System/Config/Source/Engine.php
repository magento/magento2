<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\Adminhtml\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * All registered search adapters
 *
 * @api
 * @since 100.0.2
 */
class Engine implements ArrayInterface
{
    /**
     * @param array $engines Engines list
     */
    public function __construct(
        private readonly array $engines
    ) {
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $options = [['value' => null, 'label' => __('--Please Select--')]];
        foreach ($this->engines as $key => $label) {
            $options[] = ['value' => $key, 'label' => $label];
        }
        return $options;
    }
}
