<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Developer\Model\HttpMethodUpdater;

/**
 * Logged HTTP methods usages with a controller.
 */
class Logged
{
    /**
     * @var string
     */
    private $actionClass;

    /**
     * @var string[]
     */
    private $methods;

    /**
     * Logged constructor.
     *
     * @param string   $actionClass
     * @param string[] $methods
     */
    public function __construct(string $actionClass, array $methods)
    {
        $this->actionClass = $actionClass;
        $this->methods = $methods;
    }

    /**
     * @return string
     */
    public function getActionClass(): string
    {
        return $this->actionClass;
    }

    /**
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
}
