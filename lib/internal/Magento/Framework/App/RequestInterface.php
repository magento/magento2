<?php
/**
 * Application request
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * @api
 * @since 2.0.0
 */
interface RequestInterface
{
    /**
     * Retrieve module name
     *
     * @return string
     * @since 2.0.0
     */
    public function getModuleName();

    /**
     * Set Module name
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setModuleName($name);

    /**
     * Retrieve action name
     *
     * @return string
     * @since 2.0.0
     */
    public function getActionName();

    /**
     * Set action name
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setActionName($name);

    /**
     * Retrieve param by key
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     * @since 2.0.0
     */
    public function getParam($key, $defaultValue = null);

    /**
     * Set params from key value array
     *
     * @param array $params
     * @return $this
     * @since 2.0.0
     */
    public function setParams(array $params);

    /**
     * Retrieve all params as array
     *
     * @return array
     * @since 2.0.0
     */
    public function getParams();

    /**
     * Retrieve cookie value
     *
     * @param string|null $name
     * @param string|null $default
     * @return string|null
     * @since 2.0.0
     */
    public function getCookie($name, $default);

    /**
     * Returns whether request was delivered over HTTPS
     *
     * @return bool
     * @since 2.0.0
     */
    public function isSecure();
}
