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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect index controller
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_ConfigurationController extends Mage_Core_Controller_Front_Action
{
    /**
     * Declare content type header
     *
     * @return null
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
    }

    /**
     * Initialize application
     *
     * @throws Mage_Core_Exception
     * @return Mage_XmlConnect_Model_Application
     */
    protected function _initApp()
    {
        $cookieName = Mage_XmlConnect_Model_Application::APP_CODE_COOKIE_NAME;
        $code = $this->getRequest()->getParam($cookieName);
        $screenSize = (string) $this->getRequest()->getParam(
            Mage_XmlConnect_Model_Application::APP_SCREEN_SIZE_NAME
        );
        /** @var $app Mage_XmlConnect_Model_Application */
        $app = Mage::getModel('Mage_XmlConnect_Model_Application');
        if ($app) {
            $app->loadByCode($code);
            Mage::app()->setCurrentStore(
                Mage::app()->getStore($app->getStoreId())->getCode()
            );
            Mage::getSingleton('Mage_Core_Model_Locale')->emulate($app->getStoreId());
            $app->setScreenSize($screenSize);

            if (!$app->getId()) {
                Mage::throwException($this->__('App with specified code does not exist.'));
            }

            $app->loadConfiguration();
        } else {
            Mage::throwException($this->__('App code required.'));
        }
        Mage::register('current_app', $app);
        return $app;
    }

    /**
     * Set application cookies
     *
     * Set application coolies: application code and device screen size.
     *
     * @param Mage_XmlConnect_Model_Application $app
     * @return null
     */
    protected function _initCookies(Mage_XmlConnect_Model_Application $app)
    {
        $cookieToSetArray = array(
            array(
                'cookieName'    => Mage_XmlConnect_Model_Application::APP_CODE_COOKIE_NAME,
                'paramName'     => Mage_XmlConnect_Model_Application::APP_CODE_COOKIE_NAME,
                'value'         => $app->getCode()
            ),
            array(
                'cookieName'    => Mage_XmlConnect_Model_Application::APP_SCREEN_SIZE_NAME,
                'paramName'     => Mage_XmlConnect_Model_Application::APP_SCREEN_SIZE_NAME,
                'value'         => $app->getScreenSize()
        ));

        foreach ($cookieToSetArray as $item) {
            if (!isset($_COOKIE[$item['cookieName']])
                || $_COOKIE[$item['cookieName']] != $this->getRequest()->getParam($item['paramName'])
            ) {
                /**
                 * @todo add management of cookie expire to application admin panel
                 */
                $cookieExpireOffset = 3600 * 24 * 30;
                Mage::getSingleton('Mage_Core_Model_Cookie')->set(
                    $item['cookieName'], $item['value'], $cookieExpireOffset, '/', null, null, true
                );
            }
        }
    }

    /**
     * Default action
     *
     * @return null
     */
    public function indexAction()
    {
        try {
            /** @var $app Mage_XmlConnect_Model_Application */
            $app = $this->_initApp();
            $this->_initCookies($app);

            if ($this->getRequest()->getParam('updated_at')) {
                $updatedAt = strtotime($app->getUpdatedAt());
                $loadedAt = (int) $this->getRequest()->getParam('updated_at');
                if ($loadedAt >= $updatedAt) {
                    $message = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', '<message></message>');
                    $message->addChild('status', Mage_XmlConnect_Controller_Action::MESSAGE_STATUS_SUCCESS);
                    $message->addChild('no_changes', '1');
                    $this->getResponse()->setBody($message->asNiceXml());
                    return;
                }
            }
            $this->loadLayout(false);
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), Mage_XmlConnect_Controller_Action::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            $this->_message(
                $this->__('Can\'t show configuration.'), Mage_XmlConnect_Controller_Action::MESSAGE_STATUS_ERROR
            );
            Mage::logException($e);
        }
    }

    /**
     * Generate message xml and set it to response body
     *
     * @param string $text
     * @param string $status
     * @return null
     */
    protected function _message($text, $status)
    {
        /** @var $message Mage_XmlConnect_Model_Simplexml_Element */
        $message = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', '<message></message>');
        $message->addCustomChild('status', $status);
        $message->addCustomChild('text', $text);
        $this->getResponse()->setBody($message->asNiceXml());
    }
}
