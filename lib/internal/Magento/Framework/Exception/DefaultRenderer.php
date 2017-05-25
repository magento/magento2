<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

class Renderer implements RendererInterface
{
    /**
     * Renders an exception
     *
     * @param \Exception $exception
     * @return string
     */
    public function render(\Exception $exception)
    {
        return $exception->getMessage();
    }
}
