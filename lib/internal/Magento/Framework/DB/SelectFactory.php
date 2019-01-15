<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB;

use Magento\Framework\DB\Select\SelectRenderer;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class SelectFactory
 *
 * @api
 * @since 100.1.0
 */
class SelectFactory
{
    /**
     * @var SelectRenderer
     * @since 100.1.0
     */
    protected $selectRenderer;

    /**
     * @var array
     * @since 100.1.0
     */
    protected $parts;

    /**
     * @param SelectRenderer $selectRenderer
     * @param array $parts
     * @since 100.1.0
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
     * @since 100.1.0
     */
    public function create(AdapterInterface $adapter)
    {
        return new Select($adapter, $this->selectRenderer, $this->parts);
    }
}
