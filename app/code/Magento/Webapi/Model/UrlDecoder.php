<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Model;

/**
 * Url decoder.
 */
class UrlDecoder
{
    /**
     * Decode request params.
     *
     * @param array $params
     *
     * @return array
     */
    public function decodeParams(array $params)
    {
        foreach ($params as &$param) {
            if (is_array($param)) {
                $param = $this->decodeParams($param);
            } else {
                if ($param !== null && is_string($param)) {
                    $param = rawurldecode($param);
                }
            }
        }

        return $params;
    }
}
