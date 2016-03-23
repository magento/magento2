<?php
/**
 * Application interface
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi;

/**
 * Interface for data conversion based on data type.
 */
interface ServicePayloadConverterInterface
{
    /**
     * Perform value transformation based on provided data type.
     *
     * @param mixed $data
     * @param string $type
     * @return mixed
     */
    public function convertValue($data, $type);
}
