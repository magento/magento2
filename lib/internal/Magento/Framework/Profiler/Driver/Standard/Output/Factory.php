<?php
/**
 * Standard profiler driver output factory
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Driver\Standard\Output;

use Magento\Framework\Profiler\Driver\Standard\OutputInterface;

class Factory
{
    /**
     * Default output type
     *
     * @var string
     */
    protected $_defaultOutputType;

    /**
     * Default output class prefix
     *
     * @var string
     */
    protected $_defaultOutputPrefix;

    /**
     * Constructor
     *
     * @param string $defaultOutputPrefix
     * @param string $defaultOutputType
     */
    public function __construct(
        $defaultOutputPrefix = 'Magento\Framework\Profiler\Driver\Standard\Output\\',
        $defaultOutputType = 'html'
    ) {
        $this->_defaultOutputPrefix = $defaultOutputPrefix;
        $this->_defaultOutputType = $defaultOutputType;
    }

    /**
     * Create instance of standard profiler driver output
     *
     * @param array $config
     * @return OutputInterface
     * @throws \InvalidArgumentException If driver cannot be created
     */
    public function create(array $config)
    {
        $type = isset($config['type']) ? $config['type'] : $this->_defaultOutputType;
        if (class_exists($type)) {
            $class = $type;
        } else {
            $class = $this->_defaultOutputPrefix . ucfirst($type);
            if (!class_exists($class)) {
                throw new \InvalidArgumentException(
                    sprintf("Cannot create standard driver output, class \"%s\" doesn't exist.", $class)
                );
            }
        }
        $output = new $class($config);
        if (!$output instanceof OutputInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Output class \"%s\" must implement \Magento\Framework\Profiler\Driver\Standard\OutputInterface.",
                    get_class($output)
                )
            );
        }
        return $output;
    }
}
