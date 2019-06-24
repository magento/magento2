<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Compiler\Config\Chain;

use Magento\Framework\CompiledInterception\Generator\CompiledInterceptor;
use Magento\Setup\Module\Di\Compiler\Config\ModificationInterface;

/**
 * Class CompiledInterceptorSubstitution adds required parameters to interceptor constructor
 */
class CompiledInterceptorSubstitution implements InterceptorSubstitutionInterface, ModificationInterface
{
    /**
     * @var InterceptorSubstitution
     */
    private $interceptorSubstitution;

    /**
     * @param InterceptorSubstitution $interceptorSubstitution
     */
    public function __construct(InterceptorSubstitution $interceptorSubstitution)
    {
        $this->interceptorSubstitution = $interceptorSubstitution;
    }

    /**
     * Modifies input config
     *
     * @param array $config
     * @return array
     */
    public function modify(array $config)
    {
        $config = $this->interceptorSubstitution->modify($config);

        foreach ($config['arguments'] as $instanceName => &$arguments) {
            if (substr($instanceName, -12) === '\Interceptor') {
                foreach (CompiledInterceptor::propertiesToInjectToConstructor() as $type => $name) {
                    $preference = isset($config['preferences'][$type]) ? $config['preferences'][$type] : $type;
                    foreach ($arguments as $argument) {
                        if (isset($argument['_i_']) && $argument['_i_'] == $preference) {
                            continue 2;
                        }
                    }
                    $arguments = array_merge([$name => ['_i_' => $preference]], $arguments);
                }

            }
        }

        return $config;
    }
}
