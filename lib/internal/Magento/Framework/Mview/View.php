<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Mview;

use InvalidArgumentException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Mview\View\ChangeLogBatchWalkerFactory;
use Magento\Framework\Mview\View\ChangeLogBatchWalkerInterface;
use Magento\Framework\Mview\View\ChangelogTableNotExistsException;
use Magento\Framework\Mview\View\SubscriptionFactory;
use Exception;
use Magento\Framework\Mview\View\SubscriptionInterface;

/**
 * Mview
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends DataObject implements ViewInterface, ViewSubscriptionInterface
{
    /**
     * Default batch size for partial reindex
     */
    public const DEFAULT_BATCH_SIZE = 1000;

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
     * @var ChangeLogBatchWalkerFactory
     */
    private $changeLogBatchWalkerFactory;

    /**
     * @param ConfigInterface $config
     * @param ActionFactory $actionFactory
     * @param View\StateInterface $state
     * @param View\ChangelogInterface $changelog
     * @param SubscriptionFactory $subscriptionFactory
     * @param array $data
     * @param array $changelogBatchSize
     * @param ChangeLogBatchWalkerFactory $changeLogBatchWalkerFactory
     */
    public function __construct(
        ConfigInterface $config,
        ActionFactory $actionFactory,
        View\StateInterface $state,
        View\ChangelogInterface $changelog,
        SubscriptionFactory $subscriptionFactory,
        array $data = [],
        array $changelogBatchSize = [],
        ChangeLogBatchWalkerFactory $changeLogBatchWalkerFactory = null
    ) {
        $this->config = $config;
        $this->actionFactory = $actionFactory;
        $this->state = $state;
        $this->changelog = $changelog;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->changelogBatchSize = $changelogBatchSize;
        parent::__construct($data);
        $this->changeLogBatchWalkerFactory = $changeLogBatchWalkerFactory ?:
            ObjectManager::getInstance()->get(ChangeLogBatchWalkerFactory::class);
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
        if (!$this->isIdle() || !$this->isEnabled()) {
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

            $this->executeAction($action, $lastVersionId, $currentVersionId);

            $this->getState()->loadByView($this->getId());
            $statusToRestore = $this->getState()->getStatus() === View\StateInterface::STATUS_SUSPENDED
                ? View\StateInterface::STATUS_SUSPENDED
                : View\StateInterface::STATUS_IDLE;
            $this->getState()->setVersionId($currentVersionId)->setStatus($statusToRestore)->save();
        } catch (\Throwable $exception) {
            $this->getState()->loadByView($this->getId());
            $statusToRestore = $this->getState()->getStatus() === View\StateInterface::STATUS_SUSPENDED
                ? View\StateInterface::STATUS_SUSPENDED
                : View\StateInterface::STATUS_IDLE;
            $this->getState()->setStatus($statusToRestore)->save();
            if (!$exception instanceof \Exception) {
                $exception = new \RuntimeException(
                    'Error when updating an mview',
                    0,
                    $exception
                );
            }
            throw $exception;
        }
    }

    /**
     * Execute action from last version to current version, by batches
     *
     * @param ActionInterface $action
     * @param int $lastVersionId
     * @param int $currentVersionId
     * @return void
     * @throws \Exception
     */
    private function executeAction(ActionInterface $action, int $lastVersionId, int $currentVersionId)
    {
        $batchSize = isset($this->changelogBatchSize[$this->getChangelog()->getViewId()])
            ? (int) $this->changelogBatchSize[$this->getChangelog()->getViewId()]
            : self::DEFAULT_BATCH_SIZE;

        $vsFrom = $lastVersionId;
        while ($vsFrom < $currentVersionId) {
            $walker = $this->getWalker();
            $ids = $walker->walk($this->getChangelog(), $vsFrom, $currentVersionId, $batchSize);

            if (empty($ids)) {
                break;
            }
            $vsFrom += $batchSize;
            $action->execute($ids);
        }
    }

    /**
     * Create and validate walker class for changelog
     *
     * @return ChangeLogBatchWalkerInterface|mixed
     * @throws Exception
     */
    private function getWalker(): ChangeLogBatchWalkerInterface
    {
        $config = $this->config->getView($this->changelog->getViewId());
        $walkerClass = $config['walker'];
        return $this->changeLogBatchWalkerFactory->create($walkerClass);
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
    public function initSubscriptionInstance(array $subscriptionConfig): SubscriptionInterface
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
