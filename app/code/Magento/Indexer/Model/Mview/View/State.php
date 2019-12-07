<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Mview\View;

/**
 * @method \Magento\Indexer\Model\Mview\View\State setViewId(string $value)
 */
class State extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\Mview\View\StateInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mview_state';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'mview_state';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Indexer\Model\ResourceModel\Mview\View\State $resource
     * @param \Magento\Indexer\Model\ResourceModel\Mview\View\State\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Indexer\Model\ResourceModel\Mview\View\State $resource,
        \Magento\Indexer\Model\ResourceModel\Mview\View\State\Collection $resourceCollection,
        array $data = []
    ) {
        if (!isset($data['mode'])) {
            $data['mode'] = self::MODE_DISABLED;
        }
        if (!isset($data['status'])) {
            $data['status'] = self::STATUS_IDLE;
        }
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Fill object with state data by view ID
     *
     * @param string $viewId
     * @return $this
     */
    public function loadByView($viewId)
    {
        $this->load($viewId, 'view_id');
        if (!$this->getId()) {
            $this->setViewId($viewId);
        }
        return $this;
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
     * Get state view ID
     *
     * @return string
     */
    public function getViewId()
    {
        return $this->getData('view_id');
    }

    /**
     * Get state mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->getData('mode');
    }

    /**
     * Set state mode
     *
     * @param string $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->setData('mode', $mode);
        return $this;
    }

    /**
     * Get state status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData('status');
    }

    /**
     * Set state status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->setData('status', $status);
        return $this;
    }

    /**
     * Get state updated time
     *
     * @return string
     */
    public function getUpdated()
    {
        return $this->getData('updated');
    }

    /**
     * Set state updated time
     *
     * @param string|int|\DateTimeInterface $updated
     * @return $this
     */
    public function setUpdated($updated)
    {
        $this->setData('updated', $updated);
        return $this;
    }

    /**
     * Get state version ID
     *
     * @return string
     */
    public function getVersionId()
    {
        return $this->getData('version_id');
    }

    /**
     * Set state version ID
     *
     * @param int $versionId
     * @return $this
     */
    public function setVersionId($versionId)
    {
        $this->setData('version_id', $versionId);
        return $this;
    }
}
