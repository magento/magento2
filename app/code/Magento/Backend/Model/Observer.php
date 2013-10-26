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
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backend event observer
 */
namespace Magento\Backend\Model;

class Observer
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\Core\Model\App
     */
    protected $_app;

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Core\Model\App $app
     * @param \Magento\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \Magento\Core\Model\App $app,
        \Magento\App\RequestInterface $request
    ) {
        $this->_backendSession = $backendSession;
        $this->_app = $app;
        $this->_request = $request;
    }

    /**
     * Bind locale
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Backend\Model\Observer
     */
    public function bindLocale($observer)
    {
        $locale = $observer->getEvent()->getLocale();
        if ($locale) {
            $selectedLocale = $this->_backendSession->getLocale();
            if ($selectedLocale) {
                $locale->setLocaleCode($selectedLocale);
            }
        }
        return $this;
    }

    /**
     * Prepare mass action separated data
     *
     * @return \Magento\Backend\Model\Observer
     */
    public function massactionPrepareKey()
    {
        $key = $this->_request->getPost('massaction_prepare_key');
        if ($key) {
            $postData = $this->_request->getPost($key);
            $value = is_array($postData) ? $postData : explode(',', $postData);
            $this->_request->setPost($key, $value ? $value : null);
        }
        return $this;
    }

    /**
     * Clear result of configuration files access level verification in system cache
     *
     * @return \Magento\Backend\Model\Observer
     */
    public function clearCacheConfigurationFilesAccessLevelVerification()
    {
        return $this;
    }

    /**
     * Backend will always use base class for translation.
     *
     * @return \Magento\Backend\Model\Observer
     */
    public function initializeTranslation()
    {
        return $this;
    }

    /**
     * Set url class name for store 'admin'
     *
     * @param \Magento\Event\Observer $observer
     * @return \Magento\Backend\Model\Observer
     */
    public function setUrlClassName(\Magento\Event\Observer $observer)
    {
        /** @var $storeCollection \Magento\Core\Model\Resource\Store\Collection */
        $storeCollection = $observer->getEvent()->getStoreCollection();
        /** @var $store \Magento\Core\Model\Store */
        foreach ($storeCollection as $store) {
            if ($store->getId() == 0) {
                $store->setUrlClassName('Magento\Backend\Model\Url');
                break;
            }
        }
        $this->_app->removeCache(
            \Magento\AdminNotification\Model\System\Message\Security::VERIFICATION_RESULT_CACHE_KEY
        );
        return $this;
    }
}
