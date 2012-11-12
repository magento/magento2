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
 * @package     Mage_ImportExport
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block before edit form
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Block_Adminhtml_Import_Edit_Before extends Mage_Backend_Block_Template
{
    /**
     * Core helper
     *
     * @var Mage_Core_Helper_Data
     */
    protected $_coreHelper;

    /**
     * Basic import model
     *
     * @var Mage_ImportExport_Model_Import
     */
    protected $_importModel;

    /**
     * Constructor
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Model_Layout $layout
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param Mage_Core_Model_Translate $translator
     * @param Mage_Core_Model_Cache $cache
     * @param Mage_Core_Model_Design_Package $designPackage
     * @param Mage_Core_Model_Session $session
     * @param Mage_Core_Model_Store_Config $storeConfig
     * @param Mage_Core_Controller_Varien_Front $frontController
     * @param Mage_Core_Helper_Data $coreHelper
     * @param Mage_ImportExport_Model_Import $importModel
     * @param array $data
     */
    public function __construct( Mage_Core_Controller_Request_Http $request,
        Mage_Core_Model_Layout $layout,
        Mage_Core_Model_Event_Manager $eventManager,
        Mage_Core_Model_Translate $translator,
        Mage_Core_Model_Cache $cache,
        Mage_Core_Model_Design_Package $designPackage,
        Mage_Core_Model_Session $session,
        Mage_Core_Model_Store_Config $storeConfig,
        Mage_Core_Controller_Varien_Front $frontController,
        Mage_Core_Helper_Data $coreHelper,
        Mage_ImportExport_Model_Import $importModel,
        array $data = array()
    ) {
        parent::__construct($request, $layout, $eventManager, $translator, $cache, $designPackage, $session,
            $storeConfig, $frontController, $data
        );
        $this->_coreHelper = $coreHelper;
        $this->_importModel = $importModel;
    }

    /**
     * Returns json-encoded entity behaviors array
     *
     * @return string
     */
    public function getEntityBehaviors()
    {
        $importModel = $this->_importModel;
        $behaviors = $importModel::getEntityBehaviors();
        foreach ($behaviors as $entityCode => $behavior) {
            $behaviors[$entityCode] = $behavior['code'];
        }
        return $this->_coreHelper->jsonEncode($behaviors);
    }

    /**
     * Return json-encoded list of existing behaviors
     *
     * @return string
     */
    public function getUniqueBehaviors()
    {
        $importModel = $this->_importModel;
        $uniqueBehaviors = $importModel::getUniqueEntityBehaviors();
        return $this->_coreHelper->jsonEncode(array_keys($uniqueBehaviors));
    }
}
