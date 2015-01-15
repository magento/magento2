<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model;

interface UrlInterface extends \Magento\Framework\UrlInterface
{
    /**
     * Secret key query param name
     */
    const SECRET_KEY_PARAM_NAME = 'key';

    /**
     * xpath to startup page in configuration
     */
    const XML_PATH_STARTUP_MENU_ITEM = 'admin/startup/menu_item_id';

    /**
     * Generate secret key for controller and action based on form key
     *
     * @param string $routeName
     * @param string $controller Controller name
     * @param string $action Action name
     * @return string
     */
    public function getSecretKey($routeName = null, $controller = null, $action = null);

    /**
     * Return secret key settings flag
     *
     * @return bool
     */
    public function useSecretKey();

    /**
     * Enable secret key using
     *
     * @return \Magento\Backend\Model\UrlInterface
     */
    public function turnOnSecretKey();

    /**
     * Disable secret key using
     *
     * @return \Magento\Backend\Model\UrlInterface
     */
    public function turnOffSecretKey();

    /**
     * Refresh admin menu cache etc.
     *
     * @return \Magento\Backend\Model\UrlInterface
     */
    public function renewSecretUrls();

    /**
     * Find admin start page url
     *
     * @return string
     */
    public function getStartupPageUrl();

    /**
     * Set custom auth session
     *
     * @param \Magento\Backend\Model\Auth\Session $session
     * @return \Magento\Backend\Model\UrlInterface
     */
    public function setSession(\Magento\Backend\Model\Auth\Session $session);

    /**
     * Return backend area front name, defined in configuration
     *
     * @return string
     */
    public function getAreaFrontName();

    /**
     * Find first menu item that user is able to access
     *
     * @return string
     */
    public function findFirstAvailableMenu();
}
