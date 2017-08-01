<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Api;

/**
 * Base Builder interface for simple data Objects
 * @since 2.0.0
 */
interface SimpleBuilderInterface
{
    /**
     * Builds the Data Object
     *
     * @return AbstractSimpleObject
     * @since 2.0.0
     */
    public function create();

    /**
     * Return data Object data.
     *
     * @return array
     * @since 2.0.0
     */
    public function getData();
}
