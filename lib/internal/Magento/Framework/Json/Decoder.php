<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Json;

/**
 */
class Decoder implements DecoderInterface
{
    /**
     * Decodes the given $data string which is encoded in the JSON format.
     *
     * @param string $data
     * @return mixed
     */
    public function decode($data)
    {
        return \Zend\Json\Decoder::decode($data);
    }
}
