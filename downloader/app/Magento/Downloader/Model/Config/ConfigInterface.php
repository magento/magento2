<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Connect
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class config
 *
 * @category   Magento
 * @package    Magento_Connect
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Downloader\Model\Config;

interface ConfigInterface
{
    /**
     * Set data for Settings View
     *
     * @param \Magento\Connect\Config $config
     * @param \Magento\Downloader\View $view
     * @return null
     */
    public function setInstallView($config, $view);

    /**
     * Set data for Settings View
     *
     * @param mixed $session Session object
     * @param \Magento\Downloader\View $view
     * @return null
     */
    public function setSettingsView($session, $view);

    /**
     * Set session data for Settings
     *
     * @param array $post post data
     * @param mixed $session Session object
     * @return null
     */
    public function setSettingsSession($post, $session);

    /**
     * Set config data from POST
     *
     * @param \Magento\Connect\Config $config Config object
     * @param array $post post data
     * @return boolean
     */
    public function setPostData($config, &$post);

    /**
     * Get root channel URI
     *
     * @return string Root channel URI
     */
    public function getRootChannelUri();

    /**
     * Set additional command options
     *
     * @param mixed $session Session object
     * @param array $options
     * @return null
     */
    public function setCommandOptions($session, &$options);
}
