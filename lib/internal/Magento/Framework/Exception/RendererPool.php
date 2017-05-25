<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Exception;

class RendererPool implements RendererInterface
{
    /**
     * @var RendererInterface[]
     */
    private $instances = [];

    /**
     * @param RendererInterface[] $instances
     */
    public function __construct(array $instances)
    {
        $this->instances = $instances;
    }

    /**
     * Renders an exception
     *
     * @param \Exception $exception
     * @return string
     */
    public function render(\Exception $exception)
    {
        foreach (array_reverse($this->instances) as $instance) {
            $response = $instance->render($exception);
            if ($response) {
                return $response;
            }
        }
        return '';
    }
}
