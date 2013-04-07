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
 * Composite asset that aggregates one or more assets and provides a single public file with equivalent behavior
 */
class Mage_Core_Model_Page_Asset_Merged implements Mage_Core_Model_Page_Asset_AssetInterface
{
    /**
     * @var array
     */
    private $_files = array();

    /**
     * @var string
     */
    private $_contentType;

    /**
     * @var string
     */
    private $_url;

    /**
     * @var Mage_Core_Model_Design_Package
     */
    private $_designPackage;

    /**
     * @var Mage_Core_Helper_Data
     */
    private $_coreHelper;

    /**
     * @var Magento_Filesystem
     */
    private $_filesystem;

    /**
     * @param Mage_Core_Model_Design_Package $designPackage
     * @param Mage_Core_Helper_Data $coreHelper
     * @param Magento_Filesystem $filesystem
     * @param array $assets
     * @throws InvalidArgumentException
     */
    public function __construct(
        Mage_Core_Model_Design_Package $designPackage,
        Mage_Core_Helper_Data $coreHelper,
        Magento_Filesystem $filesystem,
        array $assets
    ) {
        $this->_designPackage = $designPackage;
        $this->_coreHelper = $coreHelper;
        $this->_filesystem = $filesystem;
        if (!$assets) {
            throw new InvalidArgumentException('At least one asset has to be passed for merging.');
        }
        /** @var $asset Mage_Core_Model_Page_Asset_MergeableInterface */
        foreach ($assets as $asset) {
            if (!($asset instanceof Mage_Core_Model_Page_Asset_MergeableInterface)) {
                throw new InvalidArgumentException(
                    'Asset has to implement Mage_Core_Model_Page_Asset_MergeableInterface.'
                );
            }
            if (!$this->_contentType) {
                $this->_contentType = $asset->getContentType();
            } else if ($asset->getContentType() != $this->_contentType) {
                throw new InvalidArgumentException(
                    "Content type '{$asset->getContentType()}' cannot be merged with '{$this->_contentType}'."
                );
            }
            $this->_files[] = $asset->getSourceFile();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        if (!$this->_url) {
            $file = $this->_designPackage->mergeFiles($this->_files, $this->_contentType);
            $this->_url = $this->_designPackage->getPublicFileUrl($file);
            if ($this->_coreHelper->isStaticFilesSigned()) {
                $fileMTime = $this->_filesystem->getMTime($file());
                $this->_url .= '?' . $fileMTime;
            }
        }
        return $this->_url;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->_contentType;
    }
}
