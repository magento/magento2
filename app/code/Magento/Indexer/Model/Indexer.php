<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

use Magento\Framework\Indexer\ActionFactory;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexerInterface as IdxInterface;
use Magento\Framework\Indexer\IndexStructureInterface;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Indexer\StructureFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Indexer extends \Magento\Framework\DataObject implements IdxInterface
{
    /**
     * @var string
     */
    protected $_idFieldName = 'indexer_id';

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var StructureFactory
     */
    protected $structureFactory;

    /**
     * @var \Magento\Framework\Mview\ViewInterface
     */
    protected $view;

    /**
     * @var \Magento\Indexer\Model\Indexer\StateFactory
     */
    protected $stateFactory;

    /**
     * @var \Magento\Indexer\Model\Indexer\State
     */
    protected $state;

    /**
     * @var Indexer\CollectionFactory
     */
    protected $indexersFactory;

    /**
     * @param ConfigInterface $config
     * @param ActionFactory $actionFactory
     * @param StructureFactory $structureFactory
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @param Indexer\StateFactory $stateFactory
     * @param Indexer\CollectionFactory $indexersFactory
     * @param array $data
     */
    public function __construct(
        ConfigInterface $config,
        ActionFactory $actionFactory,
        StructureFactory $structureFactory,
        \Magento\Framework\Mview\ViewInterface $view,
        Indexer\StateFactory $stateFactory,
        Indexer\CollectionFactory $indexersFactory,
        array $data = []
    ) {
        $this->config = $config;
        $this->actionFactory = $actionFactory;
        $this->structureFactory = $structureFactory;
        $this->view = $view;
        $this->stateFactory = $stateFactory;
        $this->indexersFactory = $indexersFactory;
        parent::__construct($data);
    }

    /**
     * Return ID
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getId()
    {
        return $this->getData($this->_idFieldName);
    }

    /**
     * Set ID
     *
     * @codeCoverageIgnore
     *
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->setData($this->_idFieldName, $id);
        return $this;
    }

    /**
     * Id field name setter
     *
     * @codeCoverageIgnore
     *
     * @param  string $name
     * @return $this
     */
    public function setIdFieldName($name)
    {
        $this->_idFieldName = $name;
        return $this;
    }

    /**
     * Id field name getter
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->_idFieldName;
    }

    /**
     * Return indexer's view ID
     *
     * @return string
     */
    public function getViewId()
    {
        return $this->getData('view_id');
    }

    /**
     * Return indexer action class
     *
     * @return string
     */
    public function getActionClass()
    {
        return $this->getData('action_class');
    }

    /**
     * Return indexer title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * Return indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData('description');
    }

    /**
     * Return indexer fields
     *
     * @return array
     */
    public function getFields()
    {
        return $this->getData('fields');
    }

    /**
     * Return indexer sources
     *
     * @return array
     */
    public function getSources()
    {
        return $this->getData('sources');
    }

    /**
     * Return indexer handlers
     *
     * @return array
     */
    public function getHandlers()
    {
        return $this->getData('handlers');
    }

    /**
     * Fill indexer data from config
     *
     * @param string $indexerId
     * @return IdxInterface
     * @throws \InvalidArgumentException
     */
    public function load($indexerId)
    {
        $indexer = $this->config->getIndexer($indexerId);
        if (empty($indexer) || empty($indexer['indexer_id']) || $indexer['indexer_id'] != $indexerId) {
            throw new \InvalidArgumentException("{$indexerId} indexer does not exist.");
        }

        $this->setId($indexerId);
        $this->setData($indexer);

        return $this;
    }

    /**
     * Return related view object
     *
     * @return \Magento\Framework\Mview\ViewInterface
     */
    public function getView()
    {
        if (!$this->view->getId()) {
            $this->view->load($this->getViewId());
        }
        return $this->view;
    }

    /**
     * Return related state object
     *
     * @return StateInterface
     */
    public function getState()
    {
        if (!$this->state) {
            $this->state = $this->stateFactory->create();
            $this->state->loadByIndexer($this->getId());
        }
        return $this->state;
    }

    /**
     * Set indexer state object
     *
     * @param StateInterface $state
     * @return IdxInterface
     */
    public function setState(StateInterface $state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * Check whether indexer is run by schedule
     *
     * @return bool
     */
    public function isScheduled()
    {
        return $this->getView()->isEnabled();
    }

    /**
     * Turn scheduled mode on/off
     *
     * @param bool $scheduled
     * @return void
     */
    public function setScheduled($scheduled)
    {
        if ($scheduled) {
            $this->getView()->subscribe();
        } else {
            $this->getView()->unsubscribe();
        }
        $this->getState()->save();
    }

    /**
     * Check whether indexer is valid
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->getState()->getStatus() == StateInterface::STATUS_VALID;
    }

    /**
     * Check whether indexer is invalid
     *
     * @return bool
     */
    public function isInvalid()
    {
        return $this->getState()->getStatus() == StateInterface::STATUS_INVALID;
    }

    /**
     * Check whether indexer is working
     *
     * @return bool
     */
    public function isWorking()
    {
        return $this->getState()->getStatus() == StateInterface::STATUS_WORKING;
    }

    /**
     * Set indexer invalid
     *
     * @return void
     */
    public function invalidate()
    {
        $state = $this->getState();
        $state->setStatus(StateInterface::STATUS_INVALID);
        $state->save();
    }

    /**
     * Return indexer status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getState()->getStatus();
    }

    /**
     * Return indexer or mview latest updated time
     *
     * @return string
     */
    public function getLatestUpdated()
    {
        if ($this->getView()->isEnabled() && $this->getView()->getUpdated()) {
            if (!$this->getState()->getUpdated()) {
                return $this->getView()->getUpdated();
            }
            $indexerUpdatedDate = new \DateTime($this->getState()->getUpdated());
            $viewUpdatedDate = new \DateTime($this->getView()->getUpdated());
            if ($viewUpdatedDate > $indexerUpdatedDate) {
                return $this->getView()->getUpdated();
            }
        }
        return $this->getState()->getUpdated();
    }

    /**
     * Return indexer action instance
     *
     * @return ActionInterface
     */
    protected function getActionInstance()
    {
        return $this->actionFactory->create(
            $this->getActionClass(),
            [
                'indexStructure' => $this->getStructureInstance(),
                'data' => $this->getData(),
            ]
        );
    }

    /**
     * Return indexer structure instance
     *
     * @return IndexStructureInterface
     */
    protected function getStructureInstance()
    {
        if (!$this->getData('structure')) {
            return null;
        }
        return $this->structureFactory->create($this->getData('structure'));
    }

    /**
     * Regenerate full index
     *
     * @return void
     * @throws \Exception
     */
    public function reindexAll()
    {
        if ($this->getState()->getStatus() != StateInterface::STATUS_WORKING) {
            $state = $this->getState();
            $state->setStatus(StateInterface::STATUS_WORKING);
            $state->save();
            if ($this->getView()->isEnabled()) {
                $this->getView()->suspend();
            }
            try {
                $this->getActionInstance()->executeFull();
                $state->setStatus(StateInterface::STATUS_VALID);
                $state->save();
                $this->getView()->resume();
            } catch (\Exception $exception) {
                $state->setStatus(StateInterface::STATUS_INVALID);
                $state->save();
                $this->getView()->resume();
                throw $exception;
            }
        }
    }

    /**
     * Regenerate one row in index by ID
     *
     * @param int $id
     * @return void
     */
    public function reindexRow($id)
    {
        $this->getActionInstance()->executeRow($id);
        $this->getState()->save();
    }

    /**
     * Regenerate rows in index by ID list
     *5
     * @param int[] $ids
     * @return void
     */
    public function reindexList($ids)
    {
        $this->getActionInstance()->executeList($ids);
        $this->getState()->save();
    }
}
