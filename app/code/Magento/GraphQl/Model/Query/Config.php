<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Model\Query;

use Magento\GraphQl\Model\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class Config
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function getQueryFields()
    {
        return $this->config;
    }

    /**
     * Retrieves resolver instance configured by type name.
     *
     * @param string $type
     * @return ResolverInterface
     * @throws GraphQlInputException
     */
    public function getResolverClass($type)
    {
        if (!isset($this->config[$type]) || !isset($this->config[$type]['resolver'])) {
            throw new GraphQlInputException(__('A resolver is not defined for the type %1', $type));
        }

        return $this->config[$type]['resolver'];
    }
}
