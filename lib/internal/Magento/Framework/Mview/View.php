<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview;

class View extends \Magento\Framework\Object implements ViewInterface
{
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
     * @var \Magento\Framework\Mview\View\StateInterface
     */
    protected $state;

    /**
     * @param ConfigInterface $config
     * @param ActionFactory $actionFactory
     * @param View\StateInterface $state
     * @param View\ChangelogInterface $changelog
     * @param View\SubscriptionFactory $subscriptionFactory
     * @param array $data
     */
    public function __construct(
        ConfigInterface $config,
        ActionFactory $actionFactory,
        View\StateInterface $state,
        View\ChangelogInterface $changelog,
        View\SubscriptionFactory $subscriptionFactory,
        array $data = []
    ) {
        $this->config = $config;
        $this->actionFactory = $actionFactory;
        $this->state = $state;
        $this->changelog = $changelog;
        $this->subscriptionFactory = $subscriptionFactory;
        parent::__construct($data);
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
     * @throws \InvalidArgumentException
     */
    public function load($viewId)
    {
        $view = $this->config->getView($viewId);
        if (empty($view) || empty($view['view_id']) || $view['view_id'] != $viewId) {
            throw new \InvalidArgumentException("{$viewId} view does not exist.");
        }

        $this->setId($viewId);
        $this->setData($view);

        return $this;
    }

    /**
     * Create subscriptions
     *
     * @throws \Exception
     * @return ViewInterface
     */
    public function subscribe()
    {
        if ($this->getState()->getMode() != View\StateInterface::MODE_ENABLED) {
            try {
                // Create changelog table
                $this->getChangelog()->create();

                // Create subscriptions
                foreach ($this->getSubscriptions() as $subscription) {
                    /** @var \Magento\Framework\Mview\View\SubscriptionInterface $subscription */
                    $subscription = $this->subscriptionFactory->create(
                        [
                            'view' => $this,
                            'tableName' => $subscription['name'],
                            'columnName' => $subscription['column'],
                        ]
                    );
                    $subscription->create();
                }

                // Update view state
                $this->getState()->setMode(View\StateInterface::MODE_ENABLED)->save();
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return $this;
    }

    /**
     * Remove subscriptions
     *
     * @throws \Exception
     * @return ViewInterface
     */
    public function unsubscribe()
    {
        if ($this->getState()->getMode() != View\StateInterface::MODE_DISABLED) {
            try {
                // Remove subscriptions
                foreach ($this->getSubscriptions() as $subscription) {
                    /** @var \Magento\Framework\Mview\View\SubscriptionInterface $subscription */
                    $subscription = $this->subscriptionFactory->create(
                        [
                            'view' => $this,
                            'tableName' => $subscription['name'],
                            'columnName' => $subscription['column'],
                        ]
                    );
                    $subscription->remove();
                }

                // Drop changelog table
                $this->getChangelog()->drop();

                // Update view state
                $this->getState()->setVersionId(null)->setMode(View\StateInterface::MODE_DISABLED)->save();
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return $this;
    }

    /**
     * Materialize view by IDs in changelog
     *
     * @return void
     * @throws \Exception
     */
    public function update()
    {
        if ($this->getState()->getMode() == View\StateInterface::MODE_ENABLED &&
            $this->getState()->getStatus() == View\StateInterface::STATUS_IDLE
        ) {
            $currentVersionId = $this->getChangelog()->getVersion();
            $lastVersionId = $this->getState()->getVersionId();
            $ids = $this->getChangelog()->getList($lastVersionId, $currentVersionId);
            if ($ids) {
                $action = $this->actionFactory->get($this->getActionClass());
                $this->getState()->setStatus(View\StateInterface::STATUS_WORKING)->save();
                try {
                    $action->execute($ids);
                    $this->getState()->loadByView($this->getId());
                    $statusToRestore = $this->getState()->getStatus() ==
                        View\StateInterface::STATUS_SUSPENDED ? View\StateInterface::STATUS_SUSPENDED : View\StateInterface::STATUS_IDLE;
                    $this->getState()->setVersionId($currentVersionId)->setStatus($statusToRestore)->save();
                } catch (\Exception $exception) {
                    $this->getState()->loadByView($this->getId());
                    $statusToRestore = $this->getState()->getStatus() ==
                        View\StateInterface::STATUS_SUSPENDED ? View\StateInterface::STATUS_SUSPENDED : View\StateInterface::STATUS_IDLE;
                    $this->getState()->setStatus($statusToRestore)->save();
                    throw $exception;
                }
            }
        }
    }

    /**
     * Suspend view updates and set version ID to changelog's end
     *
     * @return void
     */
    public function suspend()
    {
        if ($this->getState()->getMode() == View\StateInterface::MODE_ENABLED) {
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
     */
    public function resume()
    {
        $state = $this->getState();
        if ($state->getStatus() == View\StateInterface::STATUS_SUSPENDED) {
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
        if ($this->getState()->getMode() == View\StateInterface::MODE_ENABLED) {
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
        return $this->getState()->getMode() == View\StateInterface::MODE_ENABLED;
    }

    /**
     * Check whether view is idle
     *
     * @return bool
     */
    public function isIdle()
    {
        return $this->getState()->getStatus() == \Magento\Framework\Mview\View\StateInterface::STATUS_IDLE;
    }

    /**
     * Check whether view is working
     *
     * @return bool
     */
    public function isWorking()
    {
        return $this->getState()->getStatus() == \Magento\Framework\Mview\View\StateInterface::STATUS_WORKING;
    }

    /**
     * Check whether view is suspended
     *
     * @return bool
     */
    public function isSuspended()
    {
        return $this->getState()->getStatus() == \Magento\Framework\Mview\View\StateInterface::STATUS_SUSPENDED;
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
}
