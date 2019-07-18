<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview;

use InvalidArgumentException;
use Magento\Framework\DataObject;
use Magento\Framework\Mview\View\ChangelogTableNotExistsException;
use Magento\Framework\Mview\View\SubscriptionFactory;
use Exception;
use Magento\Framework\Mview\View\SubscriptionInterface;

/**
 * Mview
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends DataObject implements ViewInterface
{
    /**
     * Default batch size for partial reindex
     */
    const DEFAULT_BATCH_SIZE = 1000;

    /**
     * Max versions to load from database at a time
     */
    private static $maxVersionQueryBatch = 100000;

    /**
     * @var string
     */
    protected $_idFieldName = 'view_id';

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var View\ChangelogInterface
     */
    protected $changelog;

    /**
     * @var View\SubscriptionFactory
     */
    protected $subscriptionFactory;

    /**
     * @var View\StateInterface
     */
    protected $state;

    /**
     * @var array
     */
    private $changelogBatchSize;

    /**
     * @param ConfigInterface $config
     * @param ActionFactory $actionFactory
     * @param View\StateInterface $state
     * @param View\ChangelogInterface $changelog
     * @param SubscriptionFactory $subscriptionFactory
     * @param array $data
     * @param array $changelogBatchSize
     */
    public function __construct(
        ConfigInterface $config,
        ActionFactory $actionFactory,
        View\StateInterface $state,
        View\ChangelogInterface $changelog,
        SubscriptionFactory $subscriptionFactory,
        array $data = [],
        array $changelogBatchSize = []
    ) {
        $this->config = $config;
        $this->actionFactory = $actionFactory;
        $this->state = $state;
        $this->changelog = $changelog;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->changelogBatchSize = $changelogBatchSize;
        parent::__construct($data);
    }

    /**
     * Return ID
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
     * @param string $name
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
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->_idFieldName;
    }

    /**
     * Return view action class
     *
     * @return string
     */
    public function getActionClass()
    {
        return $this->getData('action_class');
    }

    /**
     * Return view group
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->getData('group');
    }

    /**
     * Return view subscriptions
     *
     * @return array
     */
    public function getSubscriptions()
    {
        return $this->getData('subscriptions');
    }

    /**
     * Fill view data from config
     *
     * @param string $viewId
     * @return ViewInterface
     * @throws InvalidArgumentException
     */
    public function load($viewId)
    {
        $view = $this->config->getView($viewId);
        if (empty($view) || empty($view['view_id']) || $view['view_id'] !== $viewId) {
            throw new InvalidArgumentException("{$viewId} view does not exist.");
        }

        $this->setId($viewId);
        $this->setData($view);

        return $this;
    }

    /**
     * Create subscriptions
     *
     * @return ViewInterface
     * @throws Exception
     */
    public function subscribe()
    {
        if ($this->getState()->getMode() !== View\StateInterface::MODE_ENABLED) {
            // Create changelog table
            $this->getChangelog()->create();

            foreach ($this->getSubscriptions() as $subscriptionConfig) {
                $this->initSubscriptionInstance($subscriptionConfig)->create();
            }

            // Update view state
            $this->getState()->setMode(View\StateInterface::MODE_ENABLED)->save();
        }

        return $this;
    }

    /**
     * Remove subscriptions
     *
     * @return ViewInterface
     * @throws Exception
     */
    public function unsubscribe()
    {
        if ($this->getState()->getMode() != View\StateInterface::MODE_DISABLED) {
            // Remove subscriptions
            foreach ($this->getSubscriptions() as $subscriptionConfig) {
                $this->initSubscriptionInstance($subscriptionConfig)->remove();
            }

            // Update view state
            $this->getState()->setMode(View\StateInterface::MODE_DISABLED)->save();
        }

        return $this;
    }

    /**
     * Materialize view by IDs in changelog
     *
     * @return void
     * @throws Exception
     */
    public function update()
    {
        if ($this->getState()->getStatus() !== View\StateInterface::STATUS_IDLE) {
            return;
        }

        try {
            $currentVersionId = $this->getChangelog()->getVersion();
        } catch (ChangelogTableNotExistsException $e) {
            return;
        }

        $lastVersionId = (int)$this->getState()->getVersionId();
        $action = $this->actionFactory->get($this->getActionClass());

        try {
            $this->getState()->setStatus(View\StateInterface::STATUS_WORKING)->save();

            $versionBatchSize = self::$maxVersionQueryBatch;
            $batchSize = isset($this->changelogBatchSize[$this->getChangelog()->getViewId()])
                ? $this->changelogBatchSize[$this->getChangelog()->getViewId()]
                : self::DEFAULT_BATCH_SIZE;

            for ($vsFrom = $lastVersionId; $vsFrom < $currentVersionId; $vsFrom += $versionBatchSize) {
                // Don't go past the current version for atomicy.
                $versionTo = min($currentVersionId, $vsFrom + $versionBatchSize);
                $ids = $this->getChangelog()->getList($vsFrom, $versionTo);

                // We run the actual indexer in batches.
                // Chunked AFTER loading to avoid duplicates in separate chunks.
                $chunks = array_chunk($ids, $batchSize);
                foreach ($chunks as $ids) {
                    $action->execute($ids);
                }
            }

            $this->getState()->loadByView($this->getId());
            $statusToRestore = $this->getState()->getStatus() === View\StateInterface::STATUS_SUSPENDED
                ? View\StateInterface::STATUS_SUSPENDED
                : View\StateInterface::STATUS_IDLE;
            $this->getState()->setVersionId($currentVersionId)->setStatus($statusToRestore)->save();
        } catch (Exception $exception) {
            $this->getState()->loadByView($this->getId());
            $statusToRestore = $this->getState()->getStatus() === View\StateInterface::STATUS_SUSPENDED
                ? View\StateInterface::STATUS_SUSPENDED
                : View\StateInterface::STATUS_IDLE;
            $this->getState()->setStatus($statusToRestore)->save();
            throw $exception;
        }
    }

    /**
     * Suspend view updates and set version ID to changelog's end
     *
     * @return void
     * @throws Exception
     */
    public function suspend()
    {
        if ($this->getState()->getMode() === View\StateInterface::MODE_ENABLED) {
            $state = $this->getState();
            $state->setVersionId($this->getChangelog()->getVersion());
            $state->setStatus(View\StateInterface::STATUS_SUSPENDED);
            $state->save();
        }
    }

    /**
     * Resume view updates
     *
     * @return void
     * @throws Exception
     */
    public function resume()
    {
        $state = $this->getState();
        if ($state->getStatus() === View\StateInterface::STATUS_SUSPENDED) {
            $state->setStatus(View\StateInterface::STATUS_IDLE);
            $state->save();
        }
    }

    /**
     * Clear precessed changelog entries
     *
     * @return void
     */
    public function clearChangelog()
    {
        if ($this->getState()->getMode() === View\StateInterface::MODE_ENABLED) {
            $this->getChangelog()->clear($this->getState()->getVersionId());
        }
    }

    /**
     * Return related state object
     *
     * @return View\StateInterface
     */
    public function getState()
    {
        if (!$this->state->getViewId()) {
            $this->state->loadByView($this->getId());
        }
        return $this->state;
    }

    /**
     * Set view state object
     *
     * @param View\StateInterface $state
     * @return ViewInterface
     */
    public function setState(View\StateInterface $state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * Check whether view is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getState()->getMode() === View\StateInterface::MODE_ENABLED;
    }

    /**
     * Check whether view is idle
     *
     * @return bool
     */
    public function isIdle()
    {
        return $this->getState()->getStatus() === View\StateInterface::STATUS_IDLE;
    }

    /**
     * Check whether view is working
     *
     * @return bool
     */
    public function isWorking()
    {
        return $this->getState()->getStatus() === View\StateInterface::STATUS_WORKING;
    }

    /**
     * Check whether view is suspended
     *
     * @return bool
     */
    public function isSuspended()
    {
        return $this->getState()->getStatus() === View\StateInterface::STATUS_SUSPENDED;
    }

    /**
     * Return view updated datetime
     *
     * @return string
     */
    public function getUpdated()
    {
        return $this->getState()->getUpdated();
    }

    /**
     * Retrieve linked changelog
     *
     * @return View\ChangelogInterface
     */
    public function getChangelog()
    {
        if (!$this->changelog->getViewId()) {
            $this->changelog->setViewId($this->getId());
        }
        return $this->changelog;
    }

    /**
     * Initializes Subscription instance
     *
     * @param array $subscriptionConfig
     * @return SubscriptionInterface
     */
    private function initSubscriptionInstance(array $subscriptionConfig): SubscriptionInterface
    {
        return $this->subscriptionFactory->create(
            [
                'view' => $this,
                'tableName' => $subscriptionConfig['name'],
                'columnName' => $subscriptionConfig['column'],
                'subscriptionModel' => !empty($subscriptionConfig['subscription_model'])
                    ? $subscriptionConfig['subscription_model']
                    : SubscriptionFactory::INSTANCE_NAME,
            ]
        );
    }
}
