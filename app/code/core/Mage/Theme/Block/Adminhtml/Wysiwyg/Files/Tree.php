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
 * @package     Mage_Theme
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Files tree block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Theme_Block_Adminhtml_Wysiwyg_Files_Tree extends Mage_Backend_Block_Template
{
    /**
     * @var Mage_Theme_Helper_Storage
     */
    protected $_helperStorage;

    /**
     * Initialize dependencies
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Core_Model_Layout $layout
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param Mage_Backend_Model_Url $urlBuilder
     * @param Mage_Core_Model_Translate $translator
     * @param Mage_Core_Model_Cache $cache
     * @param Mage_Core_Model_Design_Package $designPackage
     * @param Mage_Core_Model_Session $session
     * @param Mage_Core_Model_Store_Config $storeConfig
     * @param Mage_Core_Controller_Varien_Front $frontController
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Core_Model_Dir $dirs
     * @param Mage_Core_Model_Logger $logger
     * @param Magento_Filesystem $filesystem
     * @param Mage_Theme_Helper_Storage $helperStorage
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Core_Model_Layout $layout,
        Mage_Core_Model_Event_Manager $eventManager,
        Mage_Backend_Model_Url $urlBuilder,
        Mage_Core_Model_Translate $translator,
        Mage_Core_Model_Cache $cache,
        Mage_Core_Model_Design_Package $designPackage,
        Mage_Core_Model_Session $session,
        Mage_Core_Model_Store_Config $storeConfig,
        Mage_Core_Controller_Varien_Front $frontController,
        Mage_Core_Model_Factory_Helper $helperFactory,
        Mage_Core_Model_Dir $dirs,
        Mage_Core_Model_Logger $logger,
        Magento_Filesystem $filesystem,
        Mage_Theme_Helper_Storage $helperStorage,
        array $data = array()
    ) {
        $this->_helperStorage = $helperStorage;
        parent::__construct($request, $layout, $eventManager, $urlBuilder, $translator, $cache, $designPackage,
            $session, $storeConfig, $frontController, $helperFactory, $dirs, $logger, $filesystem, $data);
    }

    /**
     * Json source URL
     *
     * @return string
     */
    public function getTreeLoaderUrl()
    {
        return $this->getUrl('*/*/treeJson', $this->_helperStorage->getRequestParams());
    }

    /**
     * Get tree json
     *
     * @param array $data
     * @return string
     */
    public function getTreeJson($data)
    {
        return Zend_Json::encode($data);
    }

    /**
     * Get root node name of tree
     *
     * @return string
     */
    public function getRootNodeName()
    {
        return $this->__('Storage Root');
    }

    /**
     * Return tree node full path based on current path
     *
     * @return string
     */
    public function getTreeCurrentPath()
    {
        $treePath = '/root';
        $path = $this->_helperStorage->getSession()->getCurrentPath();
        if ($path) {
            $path = str_replace($this->_helperStorage->getStorageRoot(), '', $path);
            $relative = '';
            foreach (explode(DIRECTORY_SEPARATOR, $path) as $dirName) {
                if ($dirName) {
                    $relative .= DIRECTORY_SEPARATOR . $dirName;
                    $treePath .= '/' . $this->_helperStorage->urlEncode($relative);
                }
            }
        }
        return $treePath;
    }
}
