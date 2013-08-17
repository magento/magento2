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
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backend locale model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Model_Locale extends Mage_Core_Model_Locale
{
    /**
     * @var Mage_Backend_Model_Session
     */
    protected $_session;

    /**
     * @var Mage_Backend_Model_Locale_Manager
     */
    protected $_localeManager;

    /**
     * @var Mage_Core_Controller_Request_Http
     */
    protected $_request;

    /**
     * @var Mage_Core_Model_Locale_Validator
     */
    protected $_localeValidator;

    /**
     * Constructor
     *
     * @param Mage_Backend_Model_Session $session
     * @param Mage_Backend_Model_Locale_Manager $localeManager
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Model_Locale_Validator $localeValidator
     * @param string $locale
     */
    public function __construct(
        Mage_Backend_Model_Session $session,
        Mage_Backend_Model_Locale_Manager $localeManager,
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Model_Locale_Validator $localeValidator,
        $locale=null
    ) {
        $this->_session = $session;
        $this->_localeManager = $localeManager;
        $this->_request = $request;
        $this->_localeValidator = $localeValidator;

        parent::__construct($locale);
    }

    /**
     * Set locale
     *
     * @param   string $locale
     * @return  Mage_Core_Model_LocaleInterface
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
