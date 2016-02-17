<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Writer;

use DateTime;
use Mongo;
use MongoClient;
use MongoDate;
use Traversable;
use Zend\Log\Exception;
use Zend\Log\Formatter\FormatterInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * MongoDB log writer.
 */
class MongoDB extends AbstractWriter
{
    /**
     * MongoCollection instance
     *
     * @var MongoCollection
     */
    protected $mongoCollection;

    /**
     * Options used for MongoCollection::save()
     *
     * @var array
     */
    protected $saveOptions;

    /**
     * Constructor
     *
     * @param Mongo|MongoClient|array|Traversable $mongo
     * @param string|MongoDB $database
     * @param string $collection
     * @param array $saveOptions
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($mongo, $database = null, $collection = null, array $saveOptions = array())
    {
        if ($mongo instanceof Traversable) {
            // Configuration may be multi-dimensional due to save options
            $mongo = ArrayUtils::iteratorToArray($mongo);
        }
        if (is_array($mongo)) {
            parent::__construct($mongo);
            $saveOptions = isset($mongo['save_options']) ? $mongo['save_options'] : array();
            $collection  = isset($mongo['collection']) ? $mongo['collection'] : null;
            $database    = isset($mongo['database']) ? $mongo['database'] : null;
            $mongo       = isset($mongo['mongo']) ? $mongo['mongo'] : null;
        }

        if (null === $collection) {
            throw new Exception\InvalidArgumentException('The collection parameter cannot be empty');
        }

        if (null === $database) {
            throw new Exception\InvalidArgumentException('The database parameter cannot be empty');
        }

        if (!($mongo instanceof MongoClient || $mongo instanceof Mongo)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Parameter of type %s is invalid; must be MongoClient or Mongo',
                (is_object($mongo) ? get_class($mongo) : gettype($mongo))
            ));
        }

        $this->mongoCollection = $mongo->selectCollection($database, $collection);
        $this->saveOptions     = $saveOptions;
    }

    /**
     * This writer does not support formatting.
     *
     * @param string|FormatterInterface $formatter
     * @return WriterInterface
     */
    public function setFormatter($formatter)
    {
        return $this;
    }

    /**
     * Write a message to the log.
     *
     * @param array $event Event data
     * @return void
     * @throws Exception\RuntimeException
     */
    protected function doWrite(array $event)
    {
        if (null === $this->mongoCollection) {
            throw new Exception\RuntimeException('MongoCollection must be defined');
        }

        if (isset($event['timestamp']) && $event['timestamp'] instanceof DateTime) {
            $event['timestamp'] = new MongoDate($event['timestamp']->getTimestamp());
        }

        $this->mongoCollection->save($event, $this->saveOptions);
    }
}
