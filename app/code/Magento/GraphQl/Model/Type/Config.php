<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Type;


use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class Config
{
    /**
     * @var HandlerInterface[]
     */
    private $config;

    /**
     * @param string[] $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return string[]
     */
    public function getTypes()
    {
        return $this->config;
    }

    /**
     * @param string $typeName
     * @return string
     * @throws GraphQlInputException
     */
    public function getHandlerNameForType(string $typeName)
    {
        if (!isset($this->config[$typeName])) {
            throw new GraphQlInputException(__('The %1 type has not been defined or configured', $typeName));
        }

        return $this->config[$typeName];
    }
}
