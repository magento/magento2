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

class WriterPluginManager extends AbstractPluginManager
{
    protected $aliases = array(
        'null'                 => 'noop',
        'Zend\Log\Writer\Null' => 'noop',
    );

    /**
     * Default set of writers
     *
     * @var array
     */
    protected $invokableClasses = array(
        'chromephp'      => 'Zend\Log\Writer\ChromePhp',
        'db'             => 'Zend\Log\Writer\Db',
        'fingerscrossed' => 'Zend\Log\Writer\FingersCrossed',
        'firephp'        => 'Zend\Log\Writer\FirePhp',
        'mail'           => 'Zend\Log\Writer\Mail',
        'mock'           => 'Zend\Log\Writer\Mock',
        'noop'           => 'Zend\Log\Writer\Noop',
        'stream'         => 'Zend\Log\Writer\Stream',
        'syslog'         => 'Zend\Log\Writer\Syslog',
        'zendmonitor'    => 'Zend\Log\Writer\ZendMonitor',
    );

    /**
     * Allow many writers of the same type
     *
     * @var bool
     */
    protected $shareByDefault = false;

    /**
     * Validate the plugin
     *
     * Checks that the writer loaded is an instance of Writer\WriterInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Writer\WriterInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Writer\WriterInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
