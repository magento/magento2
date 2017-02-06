<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Request\Aggregation;

/**
 * Metric
 */
class Metric
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @param string $type
     * @codeCoverageIgnore
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
     */
    public function getType()
    {
        return $this->type;
    }
}
