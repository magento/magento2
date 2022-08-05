<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\Adminhtml\System\Config\Source;

/**
 * All registered search adapters
 *
 * @api
 * @since 100.0.2
 */
class Engine implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Engines list
     *
     * @var array
     */
    private $engines;

    /**
     * @param array $engines
     */
    public function __construct(
        array $engines
    ) {
        $this->engines = $engines;
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
