<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model;

/**
 * @api
 * @since 2.0.0
 */
interface QueryInterface
{
    /**
     * @return string
     * @since 2.0.0
     */
    public function getQueryText();
}
