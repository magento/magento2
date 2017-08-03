<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Aggregation;

/**
 * Metric
 * @since 2.0.0
 */
class Metric
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $type;

    /**
     * @param string $type
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * Get Type
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getType()
    {
        return $this->type;
    }
}
