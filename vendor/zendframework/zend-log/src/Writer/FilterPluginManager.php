<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Writer;

use Zend\Log\Exception;
use Zend\Log\Filter;
use Zend\ServiceManager\AbstractPluginManager;

class FilterPluginManager extends AbstractPluginManager
{
    /**
     * Default set of filters
     *
     * @var array
     */
    protected $invokableClasses = array(
        'mock'           => 'Zend\Log\Filter\Mock',
        'priority'       => 'Zend\Log\Filter\Priority',
        'regex'          => 'Zend\Log\Filter\Regex',
        'suppress'       => 'Zend\Log\Filter\SuppressFilter',
        'suppressfilter' => 'Zend\Log\Filter\SuppressFilter',
        'validator'      => 'Zend\Log\Filter\Validator',
    );

    /**
     * Allow many filters of the same type
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Validate the plugin
     *
     * Checks that the filter loaded is an instance of Filter\FilterInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Filter\FilterInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Filter\FilterInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
