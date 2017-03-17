<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

/**
 * Magento Block interface
 *
 * @api
 */
interface RendererInterface
{
    /**
     * Produce html output using the given data source
     *
     * @param mixed $data
     * @return mixed
     */
    public function render($data);
}
