<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model\Connector\Http;

/**
 * Represents converter interface for http request and response body.
<<<<<<< HEAD
=======
 *
 * @api
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
=======

    /**
     * @return string
     */
    public function getContentMediaType(): string;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
}
