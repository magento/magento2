<?php
/**
 * Standard profiler driver output factory
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
