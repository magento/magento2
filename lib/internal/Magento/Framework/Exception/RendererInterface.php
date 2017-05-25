<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

interface RendererInterface
{
    /**
     * Renders an exception
     *
     * @param \Exception $exception
     * @return string
     */
    public function render(\Exception $exception);
}
