<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Search\Dynamic;

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
