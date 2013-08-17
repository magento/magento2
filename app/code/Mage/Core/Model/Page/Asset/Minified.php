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

/**
 * Minified page asset
 */
class Mage_Core_Model_Page_Asset_Minified implements Mage_Core_Model_Page_Asset_MergeableInterface
{

    /**
     * @var Mage_Core_Model_Page_Asset_LocalInterface
     */
    protected $_originalAsset;

    /**
     * @var Magento_Code_Minifier
     */
    protected $_minifier;

    /**
     * @var string
     */
    protected $_file;

    /**
     * @var string
     */
    protected $_url;

    /**
     * @var Mage_Core_Model_View_Url
     */
    protected $_viewUrl;

    /**
     * @var Mage_Core_Model_Logger
     */
    protected $_logger;

    /**
     * @param Mage_Core_Model_Page_Asset_LocalInterface $asset
     * @param Magento_Code_Minifier $minifier
     * @param Mage_Core_Model_View_Url $viewUrl
     * @param Mage_Core_Model_Logger $logger
     */
    public function __construct(
        Mage_Core_Model_Page_Asset_LocalInterface $asset,
        Magento_Code_Minifier $minifier,
        Mage_Core_Model_View_Url $viewUrl,
        Mage_Core_Model_Logger $logger
    ) {
        $this->_originalAsset = $asset;
        $this->_minifier = $minifier;
        $this->_viewUrl = $viewUrl;
        $this->_logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        if (empty($this->_url)) {
            $this->_process();
        }
        return $this->_url;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->_originalAsset->getContentType();
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceFile()
    {
        if (empty($this->_file)) {
            $this->_process();
        }
        return $this->_file;
    }

    /**
     * Minify content of child asset
     */
    protected function _process()
    {
        $originalFile = $this->_originalAsset->getSourceFile();

        try {
            $this->_file = $this->_minifier->getMinifiedFile($originalFile);
        } catch (Exception $e) {
            $this->_logger->logException(new Magento_Exception('Could not minify file: ' . $originalFile, 0, $e));
            $this->_file = $originalFile;
        }
        if ($this->_file == $originalFile) {
            $this->_url = $this->_originalAsset->getUrl();
        } else {
            $this->_url = $this->_viewUrl->getPublicFileUrl($this->_file);
        }
    }
}
