<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\Http;

/**
 * Represents converter interface for http request and response body.
 */
interface ConverterInterface
{
    /**
     * @param string $body
     *
     * @return array
     */
    public function fromBody($body);

    /**
     * @param array $data
     *
     * @return string
     */
    public function toBody(array $data);

    /**
     * @return string
     */
    public function getContentTypeHeader();
}
