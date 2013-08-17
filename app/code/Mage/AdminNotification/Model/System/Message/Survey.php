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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_AdminNotification_Model_System_Message_Survey
    implements Mage_AdminNotification_Model_System_MessageInterface
{
    /**
     * @var Mage_Core_Model_Factory_Helper
     */
    protected $_helperFactory;

    /**
     * @var Mage_Backend_Model_Auth_Session
     */
    protected $_authSession;

    /**
     * @var Magento_AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var Mage_Core_Model_UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Backend_Model_Auth_Session $authSession
     * @param Magento_AuthorizationInterface $authorization
     * @param Mage_Core_Model_UrlInterface $urlBuilder
     */
    public function __construct(
        Mage_Core_Model_Factory_Helper $helperFactory,
        Mage_Backend_Model_Auth_Session $authSession,
        Magento_AuthorizationInterface $authorization,
        Mage_Core_Model_UrlInterface $urlBuilder
    ) {
        $this->_helperFactory = $helperFactory;
        $this->_authorization = $authorization;
        $this->_authSession = $authSession;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * Return survey url
     *
     * @return string
     */
    public function getSurveyUrl()
    {
        return Mage_AdminNotification_Model_Survey::getSurveyUrl();
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return md5('survey' . $this->getSurveyUrl());
    }

    /**
     * Check whether survey question can show
     *
     * @return bool
     */
    public function isDisplayed()
    {
        if ($this->_authSession->getHideSurveyQuestion()
            || false == $this->_authorization->isAllowed(null)
            || Mage_AdminNotification_Model_Survey::isSurveyViewed()
            || false == Mage_AdminNotification_Model_Survey::isSurveyUrlValid()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText()
    {
        $params = array(
            'actionLink' => array(
                'event' => 'surveyYes',
                'eventData' => array(
                    'surveyUrl' => Mage_AdminNotification_Model_Survey::getSurveyUrl(),
                    'surveyAction' => $this->_urlBuilder->getUrl('*/survey/index', array('_current' => true)),
                    'decision' => 'yes',
                ),
            ),
        );
        return $this->_helperFactory->get('Mage_AdminNotification_Helper_Data')->__('We appreciate our merchants\' feedback. Please <a href="#" data-mage-init=%s>take our survey</a> and tell us about features you\'d like to see in Magento.', json_encode($params, JSON_FORCE_OBJECT));
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return Mage_AdminNotification_Model_System_MessageInterface::SEVERITY_MAJOR;
    }
}
