<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Tracking;

use Magento\Shipping\Model\Rate\Result as RateResult;
use Magento\Shipping\Model\Tracking\Result\AbstractResult;

/**
 * Class \Magento\Shipping\Model\Tracking\Result
 *
 * @since 2.0.0
 */
class Result
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_trackings = [];

    /**
     * @var null|array
     * @since 2.0.0
     */
    protected $_error = null;

    /**
     * Reset tracking
     *
     * @return $this
     * @since 2.0.0
     */
    public function reset()
    {
        $this->_trackings = [];
        return $this;
    }

    /**
     * @param array $error
     * @return void
     * @since 2.0.0
     */
    public function setError($error)
    {
        $this->_error = $error;
    }

    /**
     * @return array|null
     * @since 2.0.0
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * Add a tracking to the result
     *
     * @param AbstractResult|RateResult $result
     * @return $this
     * @since 2.0.0
     */
    public function append($result)
    {
        if ($result instanceof AbstractResult) {
            $this->_trackings[] = $result;
        } elseif ($result instanceof RateResult) {
            $trackings = $result->getAllTrackings();
            foreach ($trackings as $track) {
                $this->append($track);
            }
        }
        return $this;
    }

    /**
     * Return all trackings in the result
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllTrackings()
    {
        return $this->_trackings;
    }
}
