<?php
/**
 * Application interface
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Webapi;

/**
 * Interface for data conversion based on data type.
 *
 * @api
 * @since 100.0.2
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
