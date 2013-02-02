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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Page handles navigation control
 *
 * @method array getHierarchy() getHierarchy()
 * @method Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_HandlesHierarchy setHierarchy() setHierarchy(array $data)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_HandlesHierarchy
    extends Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_BlockAbstract
{
    /**
     * Page handle currently selected
     *
     * @var string
     */
    protected $_selectedHandle;

    /**
     * VDE url model
     *
     * @var Mage_DesignEditor_Model_Url_Handle
     */
    protected $_vdeUrlBuilder;

    /**
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
     * @param Mage_DesignEditor_Model_Url_Handle $vdeUrlBuilder
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
        Mage_DesignEditor_Model_Url_Handle $vdeUrlBuilder,
        array $data = array()
    ) {
        $this->_vdeUrlBuilder = $vdeUrlBuilder;

        parent::__construct($request, $layout, $eventManager, $urlBuilder, $translator, $cache, $designPackage,
            $session, $storeConfig, $frontController, $helperFactory, $dirs, $logger, $filesystem, $data
        );
    }

    /**
     * Recursively render each level of the page handles hierarchy
     *
     * @param array $hierarchy
     * @return string
     */
    protected function _renderHierarchy(array $hierarchy)
    {
        if (!$hierarchy) {
            return '';
        }
        $result = '<ul>';
        foreach ($hierarchy as $name => $info) {
            $linkUrl = $this->_vdeUrlBuilder->getUrl('design/page/type', array('handle' => $name));
            $class = $info['type'] == Mage_Core_Model_Layout_Merge::TYPE_FRAGMENT
                ? ' class="vde_option_fragment"'
                : '';
            $result .= '<li rel="' . $name . '"' . $class . '>';
            $result .= '<a href="' . $linkUrl. '">';
            $result .= $this->escapeHtml($info['label']);
            $result .= '</a>';
            $result .= $this->_renderHierarchy($info['children']);
            $result .= '</li>';
        }
        $result .= '</ul>';
        return $result;
    }

    /**
     * Render page handles hierarchy as an HTML list
     *
     * @return string
     */
    public function renderHierarchy()
    {
        return $this->_renderHierarchy($this->getHierarchy());
    }

    /**
     * Retrieve the name of the currently selected page handle
     *
     * @return string|null
     */
    public function getSelectedHandle()
    {
        if ($this->_selectedHandle === null) {
            $pageHandles = $this->getHierarchy();
            $defaultHandle = reset($pageHandles);
            if ($defaultHandle !== false) {
                $this->_selectedHandle = $defaultHandle['name'];
            }
        }
        return $this->_selectedHandle;
    }

    /**
     * Retrieve label for the currently selected page handle
     *
     * @return string|null
     */
    public function getSelectedHandleLabel()
    {
        return $this->escapeHtml($this->getLayout()->getUpdate()->getPageHandleLabel($this->getSelectedHandle()));
    }

    /**
     * Set the name of the currently selected page handle
     *
     * @param string $handleName Page handle name
     */
    public function setSelectedHandle($handleName)
    {
        $this->_selectedHandle = $handleName;
    }
}
