<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Writer;

use Traversable;
use FirePHP as FirePHPService;
use Zend\Log\Exception;
use Zend\Log\Formatter\FirePhp as FirePhpFormatter;
use Zend\Log\Logger;

class FirePhp extends AbstractWriter
{
    /**
     * A FirePhpInterface instance that is used to log messages to.
     *
     * @var FirePhp\FirePhpInterface
     */
    protected $firephp;

    /**
     * Initializes a new instance of this class.
     *
     * @param null|FirePhp\FirePhpInterface|array|Traversable $instance An instance of FirePhpInterface
     *        that should be used for logging
     */
    public function __construct($instance = null)
    {
        if ($instance instanceof Traversable) {
            $instance = iterator_to_array($instance);
        }

        if (is_array($instance)) {
            parent::__construct($instance);
            $instance = isset($instance['instance']) ? $instance['instance'] : null;
        }

        if ($instance !== null && !($instance instanceof FirePhp\FirePhpInterface)) {
            throw new Exception\InvalidArgumentException('You must pass a valid FirePhp\FirePhpInterface');
        }

        $this->firephp   = $instance;
        $this->formatter = new FirePhpFormatter();
    }

    /**
     * Write a message to the log.
     *
     * @param  array $event event data
     * @return void
     */
    protected function doWrite(array $event)
    {
        $firephp = $this->getFirePhp();

        if (!$firephp->getEnabled()) {
            return;
        }

        list($line, $label) = $this->formatter->format($event);

        switch ($event['priority']) {
            case Logger::EMERG:
            case Logger::ALERT:
            case Logger::CRIT:
            case Logger::ERR:
                $firephp->error($line, $label);
                break;
            case Logger::WARN:
                $firephp->warn($line, $label);
                break;
            case Logger::NOTICE:
            case Logger::INFO:
                $firephp->info($line, $label);
                break;
            case Logger::DEBUG:
                $firephp->trace($line);
                break;
            default:
                $firephp->log($line, $label);
                break;
        }
    }

    /**
     * Gets the FirePhpInterface instance that is used for logging.
     *
     * @return FirePhp\FirePhpInterface
     * @throws Exception\RuntimeException
     */
    public function getFirePhp()
    {
        if (!$this->firephp instanceof FirePhp\FirePhpInterface
            && !class_exists('FirePHP')
        ) {
            // No FirePHP instance, and no way to create one
            throw new Exception\RuntimeException('FirePHP Class not found');
        }

        // Remember: class names in strings are absolute; thus the class_exists
        // here references the canonical name for the FirePHP class
        if (!$this->firephp instanceof FirePhp\FirePhpInterface
            && class_exists('FirePHP')
        ) {
            // FirePHPService is an alias for FirePHP; otherwise the class
            // names would clash in this file on this line.
            $this->setFirePhp(new FirePhp\FirePhpBridge(new FirePHPService()));
        }

        return $this->firephp;
    }

    /**
     * Sets the FirePhpInterface instance that is used for logging.
     *
     * @param  FirePhp\FirePhpInterface $instance A FirePhpInterface instance to set.
     * @return FirePhp
     */
    public function setFirePhp(FirePhp\FirePhpInterface $instance)
    {
        $this->firephp = $instance;

        return $this;
    }
}
