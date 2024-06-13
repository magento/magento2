<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi\CustomAttribute;

/**
 * ServiceTypeListInterface interface
 *
 * @api
 */
interface ServiceTypeListInterface
{
    /**
     * Get list of all Data Interface corresponding to complex custom attribute types
     *
     * @return string[] array of Data Interface class names
     */
    public function getDataTypes();
}
