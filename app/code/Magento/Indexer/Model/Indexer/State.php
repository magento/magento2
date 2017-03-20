<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Indexer\Model\ResourceModel\Indexer\State $resource
     * @param \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Indexer\Model\ResourceModel\Indexer\State $resource,
        \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection $resourceCollection,
        array $data = []
    ) {
        if (!isset($data['status'])) {
            $data['status'] = self::STATUS_INVALID;
        }
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
}
