<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Dynamic;

/**
 * @api
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
