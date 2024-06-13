<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Search\Dynamic;

/**
 * @api
 * @since 100.0.2
 */
class EntityStorage
{
    /**
     * @var mixed
     */
    private $source;

    /**
     * @param mixed $source
     */
    public function __construct($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }
}
