<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

/**
 * Class IframeService
 * Inject this into the response control decision class for payment a method on a single request
 */
class IframeService
{
    /**
     * @var array
     */
    private $params = [];

    /**
     * @var bool
     */
    private $isParamsSet = false;

    /**
     * This method can set only once the parameters to prevent other classes from modifying response
     *
     * @param array $params
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setParams(array $params)
    {
        if (!$this->isParamsSet) {
            $this->params = $params;
            $this->isParamsSet = true;
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Parameters can not be set in the service class more than once per request.')
            );
        }
        return $this;
    }

    /**
     * This method can set only once the parameters to prevent other classes from modifying response
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getParams()
    {
        if (!$this->isParamsSet) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Parameters are not set on this request.')
            );
        }
        return $this->params;
    }
}
