<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Interface RendererInterface
 * @since 2.1.0
 */
interface RendererInterface
{
    /**
     * Render Select part
     *
     * @param Select $select
     * @param string $sql
     * @return string
     * @since 2.1.0
     */
    public function render(Select $select, $sql = '');
}
