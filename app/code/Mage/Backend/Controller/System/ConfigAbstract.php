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
 * System Configuration Abstract Controller
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Backend_Controller_System_ConfigAbstract extends Mage_Backend_Controller_ActionAbstract
{
    /**
     * Authorization model
     *
     * @var Mage_Core_Model_Authorization
     */
    protected $_authorization;

    /**
     * @var Mage_Backend_Model_Config_Structure
     */
    protected $_configStructure;

    /**
     * Authentication session
     *
     * @var Mage_Backend_Model_Auth_StorageInterface
     */
    protected $_authSession;

    /**
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Controller_Response_Http $response
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Controller_Varien_Front $frontController
     * @param Mage_Core_Model_Authorization $authorization
     * @param Mage_Backend_Model_Config_Structure $configStructure
     * @param Mage_Backend_Model_Auth_StorageInterface $authSession
     * @param Mage_Core_Model_Layout_Factory $layoutFactory
     * @param string $areaCode
     * @param array $invokeArgs
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Controller_Response_Http $response,
        Magento_ObjectManager $objectManager,
        Mage_Core_Controller_Varien_Front $frontController,
        Mage_Core_Model_Authorization $authorization,
        Mage_Backend_Model_Config_Structure $configStructure,
        Mage_Backend_Model_Auth_StorageInterface $authSession,
        Mage_Core_Model_Layout_Factory $layoutFactory,
        $areaCode = null,
        array $invokeArgs = array()
    ) {
        parent::__construct($request, $response, $objectManager, $frontController, $layoutFactory, $areaCode,
            $invokeArgs
        );

        $this->_authorization = $authorization;
        $this->_configStructure = $configStructure;
        $this->_authSession = $authSession;
    }

    /**
     * Controller pre-dispatch method
     * Check if current section is found and is allowed
     *
     * @return Mage_Backend_Controller_ActionAbstract
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $section = null;
        if (!$this->getRequest()->getParam('section')) {
            $section = $this->_configStructure->getFirstSection();
            $this->getRequest()->setParam('section', $section->getId());
        } else {
            $this->_isSectionAllowed($this->getRequest()->getParam('section'));
        }
        return $this;
    }

    /**
     * Check is allow modify system configuration
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mage_Adminhtml::config');
    }

    /**
     * Check if specified section allowed in ACL
     *
     * Will forward to deniedAction(), if not allowed.
     *
     * @param string $sectionId
     * @throws Exception
     * @return bool
     */
    protected function _isSectionAllowed($sectionId)
    {
        try {
            if (false == $this->_configStructure->getElement($sectionId)->isAllowed()) {
                throw new Exception('');
            }
            return true;
        } catch (Zend_Acl_Exception $e) {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        } catch (Exception $e) {
            $this->deniedAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
    }

    /**
     * Save state of configuration field sets
     *
     * @param array $configState
     * @return bool
     */
    protected function _saveState($configState = array())
    {
        $adminUser = $this->_authSession->getUser();
        if (is_array($configState)) {
            $extra = $adminUser->getExtra();
            if (!is_array($extra)) {
                $extra = array();
            }
            if (!isset($extra['configState'])) {
                $extra['configState'] = array();
            }
            foreach ($configState as $fieldset => $state) {
                $extra['configState'][$fieldset] = $state;
            }
            $adminUser->saveExtra($extra);
        }
        return true;
    }
}
