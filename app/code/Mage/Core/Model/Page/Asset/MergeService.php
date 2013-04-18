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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Service model responsible for making a decision of whether to use the merged asset in place of original ones
 */
class Mage_Core_Model_Page_Asset_MergeService
{
    /**#@+
     * XPaths where merging configuration resides
     */
    const XML_PATH_MERGE_CSS_FILES  = 'dev/css/merge_css_files';
    const XML_PATH_MERGE_JS_FILES   = 'dev/js/merge_files';
    /**#@-*/

    /**
     * @var Magento_ObjectManager
     */
    private $_objectManager;

    /**
     * @var Mage_Core_Model_Store_Config
     */
    private $_storeConfig;

    /**
     * @var Mage_Core_Model_Design_Package
     */
    private $_designPackage;

    /**
     * @param Magento_ObjectManager $objectManager
     * @param Mage_Core_Model_Store_Config $storeConfig
     * @param Mage_Core_Model_Design_Package $designPackage
     */
    public function __construct(Magento_ObjectManager $objectManager, Mage_Core_Model_Store_Config $storeConfig,
        Mage_Core_Model_Design_Package $designPackage
    ) {
        $this->_objectManager = $objectManager;
        $this->_storeConfig = $storeConfig;
        $this->_designPackage = $designPackage;
    }

    /**
     * Return merged assets, if merging is enabled for a given content type
     *
     * @param array $assets
     * @param string $contentType
     * @return array
     * @throws InvalidArgumentException
     */
    public function getMergedAssets(array $assets, $contentType)
    {
        $isCss = $contentType == Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS;
        $isJs = $contentType == Mage_Core_Model_Design_Package::CONTENT_TYPE_JS;
        if (!$isCss && !$isJs) {
            throw new InvalidArgumentException("Merge for content type '$contentType' is not supported.");
        }

        if ($this->_designPackage->isMergingViewFilesAllowed()) {
            $isCssMergeEnabled = $this->_storeConfig->getConfigFlag(self::XML_PATH_MERGE_CSS_FILES);
            $isJsMergeEnabled = $this->_storeConfig->getConfigFlag(self::XML_PATH_MERGE_JS_FILES);
            if (($isCss && $isCssMergeEnabled) || ($isJs && $isJsMergeEnabled)) {
                $assets = array(
                    $this->_objectManager->create('Mage_Core_Model_Page_Asset_Merged', array('assets' => $assets))
                );
            }
        }

        return $assets;
    }
}
