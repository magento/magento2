<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Processor;

class ReferenceId extends RequestId implements ProcessorInterface
{
    /**
     * Adds an identifier for the request to the log.
     *
     * This enables to filter the log for messages belonging to a specific request
     *
     * @param array $event event data
     * @return array event data
     */
    public function process(array $event)
    {
        if (isset($event['extra']['referenceId'])) {
            return $event;
        }

        if (!isset($event['extra'])) {
            $event['extra'] = array();
        }

        $event['extra']['referenceId'] = $this->getIdentifier();

        return $event;
    }

    /**
     * Sets identifier.
     *
     * @param string $identifier
     * @return self
     */
    public function setReferenceId($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Returns identifier.
     *
     * @return string
     */
    public function getReferenceId()
    {
        return $this->getIdentifier();
    }
}
