<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log;

use Zend\ServiceManager\AbstractPluginManager;

class ProcessorPluginManager extends AbstractPluginManager
{
    /**
     * Default set of processors
     *
     * @var array
     */
    protected $invokableClasses = array(
        'backtrace' => 'Zend\Log\Processor\Backtrace',
        'referenceid' => 'Zend\Log\Processor\ReferenceId',
        'requestid' => 'Zend\Log\Processor\RequestId',
    );

    /**
     * Allow many processors of the same type
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Validate the plugin
     *
     * Checks that the processor loaded is an instance of Processor\ProcessorInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Processor\ProcessorInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Processor\ProcessorInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
