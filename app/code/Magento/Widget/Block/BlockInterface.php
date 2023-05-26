<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Block;

/**
 * Widget Block Interface
 *
 * @api
 */
interface BlockInterface
{
    /**
     * Add data to the widget.
     *
     * Retains previous data in the widget.
     *
     * @param array $arr
     * @return $this
     */
    public function addData(array $arr);

    /**
     * Overwrite data in the widget.
     *
     * Param $key can be string or array.
     * If $key is string, the attribute value will be overwritten by $value.
     * If $key is an array, it will overwrite all the data in the widget.
     *
     * @param string|array $key
     * @param mixed $value
     * @return $this
     */
    public function setData($key, $value = null);
}
