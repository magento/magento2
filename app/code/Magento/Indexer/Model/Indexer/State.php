<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Indexer;

use Magento\Framework\Indexer\StateInterface;

class State extends \Magento\Framework\Model\AbstractModel implements StateInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'indexer_state';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'indexer_state';

    /**
     * @var \Magento\Framework\Lock\LockManagerInterface
     */
    private $lockManager;

    /**
     * Prefix for lock mechanism
     *
     * @var string
     */
    private $lockPrefix = 'INDEXER';

    /**
     * DeploymentConfig
     *
     * @var \Magento\Framework\App\DeploymentConfig
     */
    private $configReader;

    /**
     * Parameter with path to indexer use_application_lock config
     *
     * @var string
     */
    private $useApplicationLockConfig = 'indexer/use_application_lock';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Indexer\Model\ResourceModel\Indexer\State $resource
     * @param \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection $resourceCollection
     * @param array $data
     * @param \Magento\Framework\Lock\LockManagerInterface $lockManager
     * @param \Magento\Framework\App\DeploymentConfig $configReader
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Indexer\Model\ResourceModel\Indexer\State $resource,
        \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection $resourceCollection,
        array $data = [],
        \Magento\Framework\Lock\LockManagerInterface $lockManager = null,
        \Magento\Framework\App\DeploymentConfig $configReader = null
    ) {
        if (!isset($data['status'])) {
            $data['status'] = self::STATUS_INVALID;
        }
        $this->lockManager = $lockManager ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Lock\LockManagerInterface::class
        );
        $this->configReader = $configReader ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\App\DeploymentConfig::class
        );
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Return indexer id
     *
     * @return string
     */
    public function getIndexerId()
    {
        return parent::getIndexerId();
    }

    /**
     * Set indexer id
     *
     * @param string $value
     * @return $this
     */
    public function setIndexerId($value)
    {
        return parent::setIndexerId($value);
    }

    /**
     * Return status
     *
     * @return string
     */
    public function getStatus()
    {
        if ($this->isUseApplicationLock()) {
            if (
                parent::getStatus() == StateInterface::STATUS_WORKING &&
                !$this->lockManager->isLocked($this->lockPrefix . $this->getIndexerId())
            ) {
                return StateInterface::STATUS_INVALID;
            }
        }

        return parent::getStatus();
    }

    /**
     * Return updated
     *
     * @return string
     */
    public function getUpdated()
    {
        return parent::getUpdated();
    }

    /**
     * Set updated
     *
     * @param string $value
     * @return $this
     */
    public function setUpdated($value)
    {
        return parent::setUpdated($value);
    }

    /**
     * Fill object with state data by view ID
     *
     * @param string $indexerId
     * @return $this
     */
    public function loadByIndexer($indexerId)
    {
        $this->load($indexerId, 'indexer_id');
        if (!$this->getId()) {
            $this->setIndexerId($indexerId);
        }
        return $this;
    }

    /**
     * Status setter
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        if ($this->isUseApplicationLock()) {
            if ($status == StateInterface::STATUS_WORKING) {
                $this->lockManager->lock($this->lockPrefix . $this->getIndexerId());
            } else {
                $this->lockManager->unlock($this->lockPrefix . $this->getIndexerId());
            }
        }
        return parent::setStatus($status);
    }

    /**
     * Processing object before save data
     *
     * @return $this
     */
    public function beforeSave()
    {
        $this->setUpdated(time());
        return parent::beforeSave();
    }

    /**
     * The indexer application locking mechanism is used
     *
     * @return bool
     */
    private function isUseApplicationLock()
    {
        return $this->configReader->get($this->useApplicationLockConfig) ?: false;
    }
}
