<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
