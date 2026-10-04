<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Search\Model;

/**
 * @api
 * @since 100.0.2
 */
interface QueryInterface
{
    /**
     * @return string
     */
    public function getQueryText();
}
