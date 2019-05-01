<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Request;

/**
 * Map of HTTP methods and interfaces that an action implements in order to process them.
 */
class HttpMethodMap
{
    /**
     * @var string[]
     */
    private $map;

    /**
     * @param string[] $map
     */
    public function __construct(array $map)
    {
        $this->map = $this->processMap($map);
    }

    /**
     * Filter given map.
     *
     * @param array $map
     * @throws \InvalidArgumentException
     *
     * @return string[]
     */
    private function processMap(array $map): array
    {
        $filtered = [];
        foreach ($map as $method => $interface) {
            $interface = trim(preg_replace('/^\\\+/', '', $interface));
            if (!(interface_exists($interface) || class_exists($interface))) {
                throw new \InvalidArgumentException(
                    "Interface '$interface' does not exist"
                );
            }
            if (!$method) {
                throw new \InvalidArgumentException('Invalid method given');
            }

            $filtered[$method] = $interface;
        }

        return $filtered;
    }

    /**
     * Where keys are methods' names and values are interfaces' names.
     *
     * @return string[]
     *
     * @see \Zend\Http\Request Has list of methods as METHOD_* constants.
     */
    public function getMap(): array
    {
        return $this->map;
    }
}
