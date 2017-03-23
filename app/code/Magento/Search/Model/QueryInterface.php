<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Model;

interface QueryInterface
{
    /**
     * @return string
     */
    public function getQueryText();
}
