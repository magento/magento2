<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Model;

/**
 * Interface InfoInterface
 * @api
 * @since 2.0.0
 */
interface InfoInterface
{
    /**
     * Encrypt data
     *
     * @param string $data
     * @return string
     * @since 2.0.0
     */
    public function encrypt($data);

    /**
     * Decrypt data
     *
     * @param string $data
     * @return string
     * @since 2.0.0
     */
    public function decrypt($data);

    /**
     * Set Additional information about payment into Payment model
     *
     * @param string $key
     * @param string|null $value
     * @return mixed
     * @since 2.0.0
     */
    public function setAdditionalInformation($key, $value = null);

    /**
     * Check whether there is additional information by specified key
     *
     * @param mixed|null $key
     * @return bool
     * @since 2.0.0
     */
    public function hasAdditionalInformation($key = null);

    /**
     * Unsetter for entire additional_information value or one of its element by key
     *
     * @param string|null $key
     * @return $this
     * @since 2.0.0
     */
    public function unsAdditionalInformation($key = null);

    /**
     * Getter for entire additional_information value or one of its element by key
     *
     * @param string|null $key
     * @return mixed
     * @since 2.0.0
     */
    public function getAdditionalInformation($key = null);

    /**
     * Retrieve payment method model object
     *
     * @return \Magento\Payment\Model\MethodInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getMethodInstance();
}
