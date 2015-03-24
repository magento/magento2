<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Model;

interface InfoInterface
{
    /**
     * Encrypt data
     *
     * @param string $data
     * @return string
     */
    public function encrypt($data);

    /**
     * Decrypt data
     *
     * @param string $data
     * @return string
     */
    public function decrypt($data);

    /**
     * Set Additional information about payment into Payment model
     *
     * @param $key
     * @param null $value
     * @return mixed
     */
    public function setAdditionalInformation($key, $value = null);

    /**
     * Check whether there is additional information by specified key
     *
     * @param mixed|null $key
     * @return bool
     */
    public function hasAdditionalInformation($key = null);

    /**
     * Unsetter for entire additional_information value or one of its element by key
     *
     * @param string $key
     * @return $this
     */
    public function unsAdditionalInformation($key = null);

    /**
     * Getter for entire additional_information value or one of its element by key
     *
     * @param string $key
     * @return array|null|mixed
     */
    public function getAdditionalInformation($key = null);

    /**
     * Retrieve payment method model object
     *
     * @return \Magento\Payment\Model\MethodInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getMethodInstance();
}
