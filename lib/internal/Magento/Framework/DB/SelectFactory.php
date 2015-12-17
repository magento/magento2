<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\SelectRenderer;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;

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
     * @param Mysql $adapter
     * @return \Magento\Framework\DB\Select
     */
    public function create(Mysql $adapter)
    {
        return new Select($adapter, $this->selectRenderer, $this->parts);
    }
}
