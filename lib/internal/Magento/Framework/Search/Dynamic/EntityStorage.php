<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Dynamic;

/**
 * @api
 * @since 2.0.0
 */
class EntityStorage
{
    /**
     * @var mixed
     * @since 2.0.0
     */
    private $source;

    /**
     * @param mixed $source
     * @since 2.0.0
     */
    public function __construct($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     * @since 2.0.0
     */
    public function getSource()
    {
        return $this->source;
    }
}
