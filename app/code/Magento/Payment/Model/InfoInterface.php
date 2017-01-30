<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Model;

/**
 * Interface InfoInterface
 */
interface InfoInterface
{
    /**
     * Encrypt data
     *
     * @param string $data
     * @return string
     * @api
     */
    public function encrypt($data);

    /**
     * Decrypt data
     *
     * @param string $data
     * @return string
     * @api
     */
    public function decrypt($data);

    /**
     * Set Additional information about payment into Payment model
     *
     * @param string $key
     * @param string|null $value
     * @return mixed
     * @api
     */
    public function setAdditionalInformation($key, $value = null);

    /**
     * Check whether there is additional information by specified key
     *
     * @param mixed|null $key
     * @return bool
     * @api
     */
    public function hasAdditionalInformation($key = null);

    /**
     * Unsetter for entire additional_information value or one of its element by key
     *
     * @param string|null $key
     * @return $this
     * @api
     */
    public function unsAdditionalInformation($key = null);

    /**
     * Getter for entire additional_information value or one of its element by key
     *
     * @param string|null $key
     * @return mixed
     * @api
     */
    public function getAdditionalInformation($key = null);

    /**
     * Retrieve payment method model object
     *
     * @return \Magento\Payment\Model\MethodInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     */
    public function getMethodInstance();
}
