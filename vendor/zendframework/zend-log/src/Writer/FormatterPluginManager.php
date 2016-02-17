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
use Zend\Log\Formatter;
use Zend\ServiceManager\AbstractPluginManager;

class FormatterPluginManager extends AbstractPluginManager
{
    /**
     * Default set of formatters
     *
     * @var array
     */
    protected $invokableClasses = array(
        'base'             => 'Zend\Log\Formatter\Base',
        'simple'           => 'Zend\Log\Formatter\Simple',
        'xml'              => 'Zend\Log\Formatter\Xml',
        'db'               => 'Zend\Log\Formatter\Db',
        'errorhandler'     => 'Zend\Log\Formatter\ErrorHandler',
        'exceptionhandler' => 'Zend\Log\Formatter\ExceptionHandler',
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
     * Checks that the formatter loaded is an instance of Formatter\FormatterInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Formatter\FormatterInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Formatter\FormatterInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
