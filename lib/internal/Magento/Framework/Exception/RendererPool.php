<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

class RendererPool
{
    /**
     * Key of instance is the exception format parameter
     *
     * @var RendererInterface[]
     */
    private $rendererInstances = [];

    /**
     * @param RendererInterface[] $rendererInstances
     */
    public function __construct(array $rendererInstances)
    {
        $this->rendererInstances = $rendererInstances;
    }

    /**
     * Renders an exception
     *
     * @param \Exception $exception
     * @return RendererInterface|null
     */
    public function getRenderer(\Exception $exception)
    {
        if (isset($this->rendererInstances[get_class($exception)])) {
            return $this->rendererInstances[get_class($exception)];
        }
    }
}
