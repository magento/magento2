<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

/**
 * Class provides functionality to encode url parameters.
 */
class ParamEncoder
{
    /**
     * Encode URL param.
     *
     * @param string $string
     * @return string
     */
    public function encode($string)
    {
        return rawurlencode($string);
    }
}
