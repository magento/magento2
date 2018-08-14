<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Base Builder interface for simple data Objects
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
