<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Interface RendererInterface
 *
 * @api
 */
interface RendererInterface
{
    /**
     * Render Select part
     *
     * @param Select $select
     * @param string $sql
     * @return string
     */
    public function render(Select $select, $sql = '');
}
