<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Api;

/**
 * Base Builder interface for simple data Objects
 *
 * @api
 */
interface SimpleBuilderInterface
{
    /**
     * Builds the Data Object
     *
     * @return AbstractSimpleObject
     */
    public function create();

    /**
     * Return data Object data.
     *
     * @return array
     */
    public function getData();
}
