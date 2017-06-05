<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

interface RendererInterface
{
    /**
     * Renders an exception's data as array for a specific presentation layer
     *
     * @param \Exception $exception
     * @return array
     */
    public function render(\Exception $exception);

    /**
     * Returns the identifier towards which the renderer return data is intended
     *
     * @return string
     */
    public function getIdentifier();
}
