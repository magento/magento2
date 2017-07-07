<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model;

/**
 * @api
 */
interface QueryInterface
{
    /**
     * @return string
     */
    public function getQueryText();
}
