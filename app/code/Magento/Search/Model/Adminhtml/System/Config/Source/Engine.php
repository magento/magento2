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
 * @since 2.0.0
 */
class Engine implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Engines list
     *
     * @var array
     * @since 2.0.0
     */
    private $engines;

    /**
     * Construct
     *
     * @param array $engines
     * @since 2.0.0
     */
    public function __construct(
        array $engines
    ) {
        $this->engines = $engines;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->engines as $key => $label) {
            $options[] = ['value' => $key, 'label' => $label];
        }
        return $options;
    }
}
