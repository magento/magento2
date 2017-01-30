<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

/**
 * Order configuration model
 */
class Config
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\Collection
     */
    protected $collection;

    /**
     * Statuses per state array
     *
     * @var array
     */
    protected $stateStatuses;

    /**
     * @var array
     */
    private $statuses;

    /**
     * @var Status
     */
    protected $orderStatusFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    protected $orderStatusCollectionFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var array
     */
    protected $maskStatusesMapping = [
        \Magento\Framework\App\Area::AREA_FRONTEND => [
            \Magento\Sales\Model\Order::STATUS_FRAUD => \Magento\Sales\Model\Order::STATE_PROCESSING
        ]
    ];

    /**
     * Constructor
     *
     * @param \Magento\Sales\Model\Order\StatusFactory $orderStatusFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollectionFactory
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Sales\Model\Order\StatusFactory $orderStatusFactory,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $orderStatusCollectionFactory,
        \Magento\Framework\App\State $state
    ) {
        $this->orderStatusFactory = $orderStatusFactory;
        $this->orderStatusCollectionFactory = $orderStatusCollectionFactory;
        $this->state = $state;
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Status\Collection
     */
    protected function _getCollection()
    {
        if ($this->collection == null) {
            $this->collection = $this->orderStatusCollectionFactory->create()->joinStates();
        }
        return $this->collection;
    }

    /**
     * @param string $state
     * @return Status|null
     */
    protected function _getState($state)
    {
        foreach ($this->_getCollection() as $item) {
            if ($item->getData('state') == $state) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Retrieve default status for state
     *
     * @param   string $state
     * @return  string
     */
    public function getStateDefaultStatus($state)
    {
        $status = false;
        $stateNode = $this->_getState($state);
        if ($stateNode) {
            $status = $this->orderStatusFactory->create()->loadDefaultByState($state);
            $status = $status->getStatus();
        }
        return $status;
    }

    /**
     * Retrieve status label
     *
     * @param   string $code
     * @return  string
     */
    public function getStatusLabel($code)
    {
        $code = $this->maskStatusForArea($this->state->getAreaCode(), $code);
        $status = $this->orderStatusFactory->create()->load($code);
        return $status->getStoreLabel();
    }

    /**
     * Mask status for order for specified area
     *
     * @param string $area
     * @param string $code
     * @return string
     */
    protected function maskStatusForArea($area, $code)
    {
        if (isset($this->maskStatusesMapping[$area][$code])) {
            return $this->maskStatusesMapping[$area][$code];
        }
        return $code;
    }

    /**
     * State label getter
     *
     * @param   string $state
     * @return \Magento\Framework\Phrase|string
     */
    public function getStateLabel($state)
    {
        if ($stateItem = $this->_getState($state)) {
            $label = $stateItem->getData('label');
            return __($label);
        }
        return $state;
    }

    /**
     * Retrieve all statuses
     *
     * @return array
     */
    public function getStatuses()
    {
        $statuses = $this->orderStatusCollectionFactory->create()->toOptionHash();
        return $statuses;
    }

    /**
     * Order states getter
     *
     * @return array
     */
    public function getStates()
    {
        $states = [];
        foreach ($this->_getCollection() as $item) {
            if ($item->getState()) {
                $states[$item->getState()] = __($item->getData('label'));
            }
        }
        return $states;
    }

    /**
     * Retrieve statuses available for state
     * Get all possible statuses, or for specified state, or specified states array
     * Add labels by default. Return plain array of statuses, if no labels.
     *
     * @param mixed $state
     * @param bool $addLabels
     * @return array
     */
    public function getStateStatuses($state, $addLabels = true)
    {
        $key = md5(serialize([$state, $addLabels]));
        if (isset($this->stateStatuses[$key])) {
            return $this->stateStatuses[$key];
        }
        $statuses = [];

        if (!is_array($state)) {
            $state = [$state];
        }
        foreach ($state as $_state) {
            $stateNode = $this->_getState($_state);
            if ($stateNode) {
                $collection = $this->orderStatusCollectionFactory->create()->addStateFilter($_state)->orderByLabel();
                foreach ($collection as $item) {
                    $status = $item->getData('status');
                    if ($addLabels) {
                        $statuses[$status] = $item->getStoreLabel();
                    } else {
                        $statuses[] = $status;
                    }
                }
            }
        }
        $this->stateStatuses[$key] = $statuses;
        return $statuses;
    }

    /**
     * Retrieve states which are visible on front end
     *
     * @return array
     */
    public function getVisibleOnFrontStatuses()
    {
        return $this->_getStatuses(true);
    }

    /**
     * Get order statuses, invisible on frontend
     *
     * @return array
     */
    public function getInvisibleOnFrontStatuses()
    {
        return $this->_getStatuses(false);
    }

    /**
     * Get existing order statuses
     * Visible or invisible on frontend according to passed param
     *
     * @param bool $visibility
     * @return array
     */
    protected function _getStatuses($visibility)
    {
        if ($this->statuses == null) {
            foreach ($this->_getCollection() as $item) {
                $visible = (bool) $item->getData('visible_on_front');
                $this->statuses[$visible][] = $item->getData('status');
            }
        }
        return $this->statuses[(bool) $visibility];
    }
}
