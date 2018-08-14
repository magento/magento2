<?php
/**
 * Application interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi;

/**
 * Interface for data conversion based on data type.
 *
 * @api
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
