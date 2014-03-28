<?php
/**
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
 * @category    Magento
 * @package     Magento_Index
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract index process class
 * Predefine list of methods required by indexer
 */
namespace Magento\Index\Model\Indexer;

use Magento\Index\Model\Event;
use Magento\Index\Model\IndexerInterface;

abstract class AbstractIndexer extends \Magento\Model\AbstractModel implements IndexerInterface
{
    /**
     * @var array
     */
    protected $_matchedEntities = array();

    /**
     * Whether the indexer should be displayed on process/list page
     *
     * @var bool
     */
    protected $_isVisible = true;

    /**
     * Get Indexer name
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Get Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * Register indexer required data inside event object
     *
     * @param   Event $event
     * @return  void
     */
    abstract protected function _registerEvent(Event $event);

    /**
     * Process event based on event state data
     *
     * @param   Event $event
     * @return  $this
     */
    abstract protected function _processEvent(Event $event);

    /**
     * Register data required by process in event object
     *
     * @param Event $event
     * @return IndexerInterface
     */
    public function register(Event $event)
    {
        if ($this->matchEvent($event)) {
            $this->_registerEvent($event);
        }
        return $this;
    }

    /**
     * Process event
     *
     * @param   Event $event
     * @return  $this
     */
    public function processEvent(Event $event)
    {
        if ($this->matchEvent($event)) {
            $this->_processEvent($event);
        }
        return $this;
    }

    /**
     * Check if event can be matched by process
     *
     * @param Event $event
     * @return bool
     */
    public function matchEvent(Event $event)
    {
        $entity = $event->getEntity();
        $type = $event->getType();
        return $this->matchEntityAndType($entity, $type);
    }

    /**
     * Check if indexer matched specific entity and action type
     *
     * @param   string $entity
     * @param   string $type
     * @return  bool
     */
    public function matchEntityAndType($entity, $type)
    {
        if (isset($this->_matchedEntities[$entity])) {
            if (in_array($type, $this->_matchedEntities[$entity])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Rebuild all index data
     *
     * @return  void
     */
    public function reindexAll()
    {
        $this->_getResource()->reindexAll();
    }

    /**
     * Try dynamicly detect and call event hanler from resource model.
     * Handler name will be generated from event entity and type code
     *
     * @param   Event $event
     * @return  $this
     */
    public function callEventHandler(Event $event)
    {
        if ($event->getEntity()) {
            $method = $event->getEntity() . '_' . $event->getType();
        } else {
            $method = $event->getType();
        }
        $method = str_replace(' ', '', ucwords(str_replace('_', ' ', $method)));

        $resourceModel = $this->_getResource();
        if (method_exists($resourceModel, $method)) {
            $resourceModel->{$method}($event);
        }
        return $this;
    }

    /**
     * Whether the indexer should be displayed on process/list page
     *
     * @return bool
     */
    public function isVisible()
    {
        return $this->_isVisible;
    }
}
