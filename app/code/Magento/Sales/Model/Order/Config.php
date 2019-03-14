<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

use Magento\Framework\Exception\LocalizedException;

/**
 * Order configuration model
 *
 * @api
 * @since 100.0.2
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
            \Magento\Sales\Model\Order::STATUS_FRAUD => \Magento\Sales\Model\Order::STATE_PROCESSING,
            \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW => \Magento\Sales\Model\Order::STATE_PROCESSING
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
     * Get collection.
     *
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
     * Get state.
     *
     * @param string $state
     * @return Status
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
     * @return  string|null
     */
    public function getStateDefaultStatus($state): ?string
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
     * Get status label for a specified area
     *
     * @param string|null $code
     * @param string $area
     * @return string|null
     */
    private function getStatusLabelForArea(?string $code, string $area): ?string
    {
        $code = $this->maskStatusForArea($area, $code);
        $status = $this->orderStatusFactory->create()->load($code);

        if ($area === 'adminhtml') {
            return $status->getLabel();
        }

        return $status->getStoreLabel();
    }

    /**
     * Retrieve status label for detected area
     *
     * @param string|null $code
     * @return string|null
     * @throws LocalizedException
     */
    public function getStatusLabel($code)
    {
        $area = $this->state->getAreaCode() ?: \Magento\Framework\App\Area::AREA_FRONTEND;
        return $this->getStatusLabelForArea($code, $area);
    }

    /**
     * Retrieve status label for area
     *
     * @param string|null $code
     * @return string|null
     * @since 102.0.1
     */
    public function getStatusFrontendLabel(?string $code): ?string
    {
        return $this->getStatusLabelForArea($code, \Magento\Framework\App\Area::AREA_FRONTEND);
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
        $key = sha1(json_encode([$state, $addLabels]));
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
                        $statuses[$status] = $this->getStatusLabel($status);
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
     * Get existing order statuses.
     *
     * Visible or invisible on frontend according to passed param.
     *
     * @param bool $visibility
     * @return array
     */
    protected function _getStatuses($visibility)
    {
        if ($this->statuses == null) {
            $this->statuses = [
                true => [],
                false => [],
            ];
            foreach ($this->_getCollection() as $item) {
                $visible = (bool) $item->getData('visible_on_front');
                $this->statuses[$visible][] = $item->getData('status');
            }
        }
        return $this->statuses[(bool) $visibility];
    }

    /**
     * Retrieve label by state  and status
     *
     * @param string $state
     * @param string $status
     * @return \Magento\Framework\Phrase|string
     * @since 101.0.0
     */
    public function getStateLabelByStateAndStatus($state, $status)
    {
        foreach ($this->_getCollection() as $item) {
            if ($item->getData('state') == $state && $item->getData('status') == $status) {
                $label = $item->getData('label');
                return __($label);
            }
        }
        return $state;
    }
}
