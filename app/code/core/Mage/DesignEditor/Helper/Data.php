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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Design Editor main helper
 */
class Mage_DesignEditor_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**#@+
     * XML paths to VDE settings
     */
    const XML_PATH_FRONT_NAME           = 'vde/design_editor/frontName';
    const XML_PATH_DISABLED_CACHE_TYPES = 'vde/design_editor/disabledCacheTypes';
    const XML_PATH_BLOCK_WHITE_LIST     = 'vde/design_editor/block/white_list';
    const XML_PATH_BLOCK_BLACK_LIST     = 'vde/design_editor/block/black_list';
    const XML_PATH_CONTAINER_WHITE_LIST = 'vde/design_editor/container/white_list';
    /**#@-*/

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_configuration;

    /**
     * @param Mage_Core_Model_Config $configuration
     */
    public function __construct(Mage_Core_Model_Config $configuration)
    {
        $this->_configuration = $configuration;
    }

    /**
     * Get VDE front name prefix
     *
     * @return string
     */
    public function getFrontName()
    {
        return (string)$this->_configuration->getNode(self::XML_PATH_FRONT_NAME);
    }

    /**
     * Get disabled cache types in VDE mode
     *
     * @return array
     */
    public function getDisabledCacheTypes()
    {
        $cacheTypes = explode(',', $this->_configuration->getNode(self::XML_PATH_DISABLED_CACHE_TYPES));
        $resultCacheTypes = array();
        foreach ($cacheTypes as $cacheType) {
            $resultCacheTypes[] = trim($cacheType);
        }

        return $resultCacheTypes;
    }

    /**
     * Get list of configuration element values
     *
     * @param string $xmlPath
     * @return array
     */
    protected function _getElementsList($xmlPath)
    {
        $elements = array();
        $node = $this->_configuration->getNode($xmlPath);
        if ($node) {
            $data = $node->asArray();
            if (is_array($data)) {
                $elements = array_values($data);
            }
        }
        return $elements;
    }

    /**
     * Get list of allowed blocks
     *
     * @return array
     */
    public function getBlockWhiteList()
    {
        return $this->_getElementsList(self::XML_PATH_BLOCK_WHITE_LIST);
    }

    /**
     * Get list of not allowed blocks
     *
     * @return array
     */
    public function getBlockBlackList()
    {
        return $this->_getElementsList(self::XML_PATH_BLOCK_BLACK_LIST);
    }

    /**
     * Get list of allowed blocks
     *
     * @return array
     */
    public function getContainerWhiteList()
    {
        return $this->_getElementsList(self::XML_PATH_CONTAINER_WHITE_LIST);
    }

}
