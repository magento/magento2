<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\SelectRenderer;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class SelectFactory
 */
class SelectFactory
{
    /**
     * @var SelectRenderer
     */
    protected $selectRenderer;

    /**
     * @var array
     */
    protected $parts;

    /**
     * @var Select
     */
    protected $prototype;

    /**
     * @param SelectRenderer $selectRenderer
     * @param array $parts
     */
    public function __construct(
        SelectRenderer $selectRenderer,
        $parts = []
    ) {
        $this->selectRenderer = $selectRenderer;
        $this->parts = $parts;
    }

    /**
     * @param AdapterInterface $adapter
     * @return \Magento\Framework\DB\Select
     */
    public function create(AdapterInterface $adapter)
    {
        if (!$this->prototype) {
            $this->prototype = new Select($adapter, $this->selectRenderer, $this->parts);
        }
        return clone $this->prototype;
    }
}
