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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Install\Model\System\Message;

class Survey implements \Magento\Framework\Notification\MessageInterface
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Install\Model\Survey
     */
    protected $_survey;

    /**
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Install\Model\Survey $survey
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Install\Model\Survey $survey
    ) {
        $this->_authorization = $authorization;
        $this->_authSession = $authSession;
        $this->_urlBuilder = $urlBuilder;
        $this->_survey = $survey;
    }

    /**
     * Return survey url
     *
     * @return string
     */
    public function getSurveyUrl()
    {
        return $this->_survey->getSurveyUrl();
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
        if ($this->_authSession->getHideSurveyQuestion() || false == $this->_authorization->isAllowed(
            null
        ) || $this->_survey->isSurveyViewed() || false == $this->_survey->isSurveyUrlValid()
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
                    'surveyUrl' => $this->_survey->getSurveyUrl(),
                    'surveyAction' => $this->_urlBuilder->getUrl('*/survey/index', array('_current' => true)),
                    'decision' => 'yes'
                )
            )
        );
        return __(
            'We appreciate our merchants\' feedback. Please <a href="#" data-mage-init="%1">take our survey</a> and tell us about features you\'d like to see in Magento.',
            json_encode($params, JSON_FORCE_OBJECT)
        );
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return \Magento\Framework\Notification\MessageInterface::SEVERITY_MAJOR;
    }
}
