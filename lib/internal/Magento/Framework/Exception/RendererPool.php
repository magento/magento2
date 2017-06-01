<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

class RendererPool implements RendererInterface
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
     * @return string
     */
    public function render(\Exception $exception)
    {
        if (isset($this->rendererInstances[get_class($exception)])) {
            $instance = $this->rendererInstances[get_class($exception)];
            return $instance->render($exception);
        } else {
            return $exception->getMessage();
        }
    }
}
