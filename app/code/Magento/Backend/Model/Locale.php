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
 * Backend locale model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Backend\Model;

class Locale extends \Magento\Core\Model\Locale
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Backend\Model\Locale\Manager
     */
    protected $_localeManager;

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Core\Model\Locale\Validator
     */
    protected $_localeValidator;

    /**
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Backend\Model\Locale\Manager $localeManager
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\Core\Model\Locale\Validator $localeValidator
     * @param \Magento\Core\Helper\Translate $translate
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\App\State $appState
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Locale\Config $config
     * @param \Magento\Core\Model\App $app
     * @param string $locale
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Backend\Model\Session $session,
        \Magento\Backend\Model\Locale\Manager $localeManager,
        \Magento\App\RequestInterface $request,
        \Magento\Core\Model\Locale\Validator $localeValidator,
        \Magento\Core\Helper\Translate $translate,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\App\State $appState,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Locale\Config $config,
        \Magento\Core\Model\App $app,
        $locale = null
    ) {
        $this->_session = $session;
        $this->_localeManager = $localeManager;
        $this->_request = $request;
        $this->_localeValidator = $localeValidator;
        parent::__construct(
            $eventManager, $translate, $coreStoreConfig, $appState, $storeManager, $config, $app, $locale
        );
    }

    /**
     * Set locale
     *
     * @param   string $locale
     * @return  \Magento\Core\Model\LocaleInterface
     */
    public function setLocale($locale = null)
    {
        parent::setLocale($locale);

        $forceLocale = $this->_request->getParam('locale', null);
        if (!$this->_localeValidator->isValid($forceLocale)) {
            $forceLocale = false;
        }

        $sessionLocale = $this->_session->getSessionLocale();
        $userLocale = $this->_localeManager->getUserInterfaceLocale();

        $localeCodes = array_filter(array($forceLocale, $sessionLocale, $userLocale));

        if (count($localeCodes)) {
            $this->setLocaleCode(reset($localeCodes));
        }

        return $this;
    }
}
