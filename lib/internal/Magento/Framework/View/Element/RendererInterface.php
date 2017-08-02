<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

/**
 * Magento Block interface
 *
 * @api
 * @since 2.0.0
 */
interface RendererInterface
{
    /**
     * Produce html output using the given data source
     *
     * @param mixed $data
     * @return mixed
     * @since 2.0.0
     */
    public function render($data);
}
