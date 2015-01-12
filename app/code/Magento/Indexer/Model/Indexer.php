<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model;

class Indexer extends \Magento\Framework\Object implements IndexerInterface
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
     * @param \Magento\Framework\Mview\ViewInterface $view
     * @param Indexer\StateFactory $stateFactory
     * @param Indexer\CollectionFactory $indexersFactory
     * @param array $data
     */
    public function __construct(
        ConfigInterface $config,
        ActionFactory $actionFactory,
        \Magento\Framework\Mview\ViewInterface $view,
        Indexer\StateFactory $stateFactory,
        Indexer\CollectionFactory $indexersFactory,
        array $data = []
    ) {
        $this->config = $config;
        $this->actionFactory = $actionFactory;
        $this->view = $view;
        $this->stateFactory = $stateFactory;
        $this->indexersFactory = $indexersFactory;
        parent::__construct($data);
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
     * Fill indexer data from config
     *
     * @param string $indexerId
     * @return IndexerInterface
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
     * @return Indexer\State
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
     * @param Indexer\State $state
     * @return IndexerInterface
     */
    public function setState(Indexer\State $state)
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
        return $this->getState()->getStatus() == Indexer\State::STATUS_VALID;
    }

    /**
     * Check whether indexer is invalid
     *
     * @return bool
     */
    public function isInvalid()
    {
        return $this->getState()->getStatus() == Indexer\State::STATUS_INVALID;
    }

    /**
     * Check whether indexer is working
     *
     * @return bool
     */
    public function isWorking()
    {
        return $this->getState()->getStatus() == Indexer\State::STATUS_WORKING;
    }

    /**
     * Set indexer invalid
     *
     * @return void
     */
    public function invalidate()
    {
        $state = $this->getState();
        $state->setStatus(Indexer\State::STATUS_INVALID);
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
            $indexerUpdatedDate = new \Magento\Framework\Stdlib\DateTime\Date($this->getState()->getUpdated());
            $viewUpdatedDate = new \Magento\Framework\Stdlib\DateTime\Date($this->getView()->getUpdated());
            if ($viewUpdatedDate->compare($indexerUpdatedDate) == 1) {
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
        return $this->actionFactory->get($this->getActionClass());
    }

    /**
     * Regenerate full index
     *
     * @return void
     * @throws \Exception
     */
    public function reindexAll()
    {
        if ($this->getState()->getStatus() != Indexer\State::STATUS_WORKING) {
            $state = $this->getState();
            $state->setStatus(Indexer\State::STATUS_WORKING);
            $state->save();
            if ($this->getView()->isEnabled()) {
                $this->getView()->suspend();
            }
            try {
                $this->getActionInstance()->executeFull();
                $state->setStatus(Indexer\State::STATUS_VALID);
                $state->save();
                $this->getView()->resume();
            } catch (\Exception $exception) {
                $state->setStatus(Indexer\State::STATUS_INVALID);
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
     *
     * @param int[] $ids
     * @return void
     */
    public function reindexList($ids)
    {
        $this->getActionInstance()->executeList($ids);
        $this->getState()->save();
    }
}
