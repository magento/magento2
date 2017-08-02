<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Indexer;

use Magento\Framework\Indexer\StateInterface;

/**
 * Class \Magento\Indexer\Model\Indexer\State
 *
 * @since 2.0.0
 */
class State extends \Magento\Framework\Model\AbstractModel implements StateInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'indexer_state';

    /**
     * Parameter name in event
     *
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'indexer_state';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Indexer\Model\ResourceModel\Indexer\State $resource
     * @param \Magento\Indexer\Model\ResourceModel\Indexer\State\Collection $resourceCollection
     * @param array $data
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setIndexerId($value)
    {
        return parent::setIndexerId($value);
    }

    /**
     * Return status
     *
     * @return string
     * @since 2.0.0
     */
    public function getStatus()
    {
        return parent::getStatus();
    }

    /**
     * Return updated
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setStatus($status)
    {
        return parent::setStatus($status);
    }

    /**
     * Processing object before save data
     *
     * @return $this
     * @since 2.0.0
     */
    public function beforeSave()
    {
        $this->setUpdated(time());
        return parent::beforeSave();
    }
}
