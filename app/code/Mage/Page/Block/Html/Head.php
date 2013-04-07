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
 * @package     Mage_Page
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Html page block
 *
 * @category   Mage
 * @package    Mage_Page
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Page_Block_Html_Head extends Mage_Core_Block_Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'html/head.phtml';

    /**
     * Chunks of title (necessary for backend)
     *
     * @var array
     */
    protected $_titleChunks;

    /**
     * Page title without prefix and suffix when not chunked
     *
     * @var string
     */
    protected $_pureTitle;

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_Page_Asset_MergeService
     */
    private $_assetMergeService;

    /**
     * @var Mage_Page_Model_Asset_GroupedCollection
     */
    private $_pageAssets;

    public function __construct(
        Mage_Core_Block_Template_Context $context,
        Magento_ObjectManager $objectManager,
        Mage_Core_Model_Page $page,
        Mage_Core_Model_Page_Asset_MergeService $assetMergeService,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
        $this->_assetMergeService = $assetMergeService;
        $this->_pageAssets = $page->getAssets();
    }

    /**
     * Add CSS file to HEAD entity
     *
     * @param string $file
     * @param string $attributes
     * @param string|null $ieCondition
     * @param string|null $flagName
     * @return Mage_Page_Block_Html_Head
     */
    public function addCss($file, $attributes = '', $ieCondition = null, $flagName = null)
    {
        $contentType = Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS;
        $asset = $this->_objectManager->create(
            'Mage_Core_Model_Page_Asset_ViewFile', array('file' => (string)$file, 'contentType' => $contentType)
        );
        $this->_pageAssets->add("$contentType/$file", $asset, array(
            'attributes'    => (string)$attributes,
            'ie_condition'  => (string)$ieCondition,
            'flag_name'     => (string)$flagName,
        ));
        return $this;
    }

    /**
     * Add JavaScript file to HEAD entity
     *
     * @param string $file
     * @param string $attributes
     * @param string|null $ieCondition
     * @param string|null $flagName
     * @return Mage_Page_Block_Html_Head
     */
    public function addJs($file, $attributes = '', $ieCondition = null, $flagName = null)
    {
        $contentType = Mage_Core_Model_Design_Package::CONTENT_TYPE_JS;
        $asset = $this->_objectManager->create(
            'Mage_Core_Model_Page_Asset_ViewFile', array('file' => (string)$file, 'contentType' => $contentType)
        );
        $this->_pageAssets->add("$contentType/$file", $asset, array(
            'attributes'    => (string)$attributes,
            'ie_condition'  => (string)$ieCondition,
            'flag_name'     => (string)$flagName,
        ));
        return $this;
    }

    /**
     * Add CSS file for Internet Explorer only to HEAD entity
     *
     * @param string $file
     * @param string $attributes
     * @param string|null $flagName
     * @return Mage_Page_Block_Html_Head
     */
    public function addCssIe($file, $attributes = '', $flagName = null)
    {
        return $this->addCss($file, $attributes, 'IE', $flagName);
    }

    /**
     * Add JavaScript file for Internet Explorer only to HEAD entity
     *
     * @param string $file
     * @param string $attributes
     * @param string|null $flagName
     * @return Mage_Page_Block_Html_Head
     */
    public function addJsIe($file, $attributes = '', $flagName = null)
    {
        return $this->addJs($file, $attributes, 'IE', $flagName);
    }

    /**
     * Add RSS element to HEAD entity
     *
     * @param string $title
     * @param string $href
     * @return Mage_Page_Block_Html_Head
     */
    public function addRss($title, $href)
    {
        $attributes = 'rel="alternate" type="application/rss+xml" title="' . $title . '"';
        $asset = $this->_objectManager->create(
            'Mage_Core_Model_Page_Asset_Remote', array('url' => (string)$href)
        );
        $this->_pageAssets->add("link/$href", $asset, array('attributes' => $attributes));
        return $this;
    }

    /**
     * Add Link element to HEAD entity
     *
     * @param string $rel forward link types
     * @param string $href URI for linked resource
     * @return Mage_Page_Block_Html_Head
     */
    public function addLinkRel($rel, $href)
    {
        $asset = $this->_objectManager->create(
            'Mage_Core_Model_Page_Asset_Remote', array('url' => (string)$href)
        );
        $this->_pageAssets->add("link/$href", $asset, array('attributes' => 'rel="' . $rel . '"'));
        return $this;
    }

    /**
     * Remove Item from HEAD entity
     *
     * @param string $type
     * @param string $name
     * @return Mage_Page_Block_Html_Head
     */
    public function removeItem($type, $name)
    {
        $this->_pageAssets->remove("$type/$name");
        return $this;
    }

    /**
     * Render HTML for the added head items
     *
     * @return string
     */
    public function getCssJsHtml()
    {
        $result = '';
        /** @var $group Mage_Page_Model_Asset_PropertyGroup */
        foreach ($this->_pageAssets->getGroups() as $group) {
            $contentType = $group->getProperty(Mage_Page_Model_Asset_GroupedCollection::PROPERTY_CONTENT_TYPE);
            $canMerge = $group->getProperty(Mage_Page_Model_Asset_GroupedCollection::PROPERTY_CAN_MERGE);
            $attributes = $group->getProperty('attributes');
            $ieCondition = $group->getProperty('ie_condition');
            $flagName = $group->getProperty('flag_name');

            if ($flagName && !$this->getData($flagName)) {
                continue;
            }

            $groupAssets = $group->getAll();
            if ($canMerge && count($groupAssets) > 1) {
                $groupAssets = $this->_assetMergeService->getMergedAssets($groupAssets, $contentType);
            }

            if (!empty($attributes)) {
                $attributes = ' ' . $attributes;
            }
            if ($contentType == Mage_Core_Model_Design_Package::CONTENT_TYPE_JS ) {
                $groupTemplate = '<script' . $attributes . ' type="text/javascript" src="%s"></script>' . "\n";
            } else {
                if ($contentType == Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS) {
                    $attributes = ' rel="stylesheet" type="text/css"' . ($attributes ?: ' media="all"');
                }
                $groupTemplate = '<link' . $attributes . ' href="%s" />' . "\n";
            }

            $groupHtml = $this->_renderHtml($groupTemplate, $groupAssets);

            if (!empty($ieCondition)) {
                $groupHtml = '<!--[if ' . $ieCondition . ']>' . "\n" . $groupHtml . '<![endif]-->' . "\n";
            }

            $result .= $groupHtml;
        }
        return $result;
    }

    /**
     * Render HTML tags referencing corresponding URLs
     *
     * @param string $template
     * @param array $assets
     * @return string
     */
    protected function _renderHtml($template, array $assets)
    {
        $result = '';
        try {
            /** @var $asset Mage_Core_Model_Page_Asset_AssetInterface */
            foreach ($assets as $asset) {
                $result .= sprintf($template, $asset->getUrl());
            }
        } catch (Magento_Exception $e) {
            $result .= sprintf($template, $this->_getNotFoundUrl());
        }
        return $result;
    }

    /**
     * Retrieve Content Type
     *
     * @return string
     */
    public function getContentType()
    {
        if (empty($this->_data['content_type'])) {
            $this->_data['content_type'] = $this->getMediaType() . '; charset=' . $this->getCharset();
        }
        return $this->_data['content_type'];
    }

    /**
     * Retrieve Media Type
     *
     * @return string
     */
    public function getMediaType()
    {
        if (empty($this->_data['media_type'])) {
            $this->_data['media_type'] = Mage::getStoreConfig('design/head/default_media_type');
        }
        return $this->_data['media_type'];
    }

    /**
     * Retrieve Charset
     *
     * @return string
     */
    public function getCharset()
    {
        if (empty($this->_data['charset'])) {
            $this->_data['charset'] = Mage::getStoreConfig('design/head/default_charset');
        }
        return $this->_data['charset'];
    }

    /**
     * Set title element text
     *
     * @param string|array $title
     * @return Mage_Page_Block_Html_Head
     */
    public function setTitle($title)
    {
        if (is_array($title)) {
            $this->_titleChunks = $title;
            $title = implode(' / ', $title);
        } else {
            $this->_pureTitle = $title;
        }
        $this->_data['title'] = Mage::getStoreConfig('design/head/title_prefix') . ' ' . $title
            . ' ' . Mage::getStoreConfig('design/head/title_suffix');
        return $this;
    }

    /**
     * Retrieve title element text (encoded)
     *
     * @return string
     */
    public function getTitle()
    {
        if (empty($this->_data['title'])) {
            $this->_data['title'] = $this->getDefaultTitle();
        }
        return htmlspecialchars(html_entity_decode(trim($this->_data['title']), ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Same as getTitle(), but return only first item from chunk for backend pages
     *
     * @return mixed|string
     */
    public function getShortTitle()
    {
        if (!empty($this->_titleChunks)) {
            return reset($this->_titleChunks);
        } else {
            return $this->_pureTitle;
        }
    }

    /**
     * Retrieve default title text
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return Mage::getStoreConfig('design/head/default_title');
    }

    /**
     * Retrieve content for description tag
     *
     * @return string
     */
    public function getDescription()
    {
        if (empty($this->_data['description'])) {
            $this->_data['description'] = Mage::getStoreConfig('design/head/default_description');
        }
        return $this->_data['description'];
    }

    /**
     * Retrieve content for keyvords tag
     *
     * @return string
     */
    public function getKeywords()
    {
        if (empty($this->_data['keywords'])) {
            $this->_data['keywords'] = Mage::getStoreConfig('design/head/default_keywords');
        }
        return $this->_data['keywords'];
    }

    /**
     * Retrieve URL to robots file
     *
     * @return string
     */
    public function getRobots()
    {
        if (empty($this->_data['robots'])) {
            $this->_data['robots'] = Mage::getStoreConfig('design/search_engine_robots/default_robots');
        }
        return $this->_data['robots'];
    }

    /**
     * Get miscellaneous scripts/styles to be included in head before head closing tag
     *
     * @return string
     */
    public function getIncludes()
    {
        if (empty($this->_data['includes'])) {
            $this->_data['includes'] = Mage::getStoreConfig('design/head/includes');
        }
        return $this->_data['includes'];
    }

    /**
     * Getter for path to Favicon
     *
     * @return string
     */
    public function getFaviconFile()
    {
        if (empty($this->_data['favicon_file'])) {
            $this->_data['favicon_file'] = $this->_getFaviconFile();
        }
        return $this->_data['favicon_file'];
    }

    /**
     * Retrieve path to Favicon
     *
     * @return string
     */
    protected function _getFaviconFile()
    {
        $folderName = Mage_Backend_Model_Config_Backend_Image_Favicon::UPLOAD_DIR;
        $storeConfig = Mage::getStoreConfig('design/head/shortcut_icon');
        $faviconFile = Mage::getBaseUrl('media') . $folderName . '/' . $storeConfig;
        $absolutePath = Mage::getBaseDir('media') . '/' . $folderName . '/' . $storeConfig;

        if (!is_null($storeConfig) && $this->_isFile($absolutePath)) {
            $url = $faviconFile;
        } else {
            $url = $this->getViewFileUrl('Mage_Page::favicon.ico');
        }
        return $url;
    }

    /**
     * If DB file storage is on - find there, otherwise - just file_exists
     *
     * @param string $filename
     * @return bool
     */
    protected function _isFile($filename)
    {
        if (Mage::helper('Mage_Core_Helper_File_Storage_Database')->checkDbUsage() && !is_file($filename)) {
            Mage::helper('Mage_Core_Helper_File_Storage_Database')->saveFileToFilesystem($filename);
        }
        return is_file($filename);
    }

    /**
     * Retrieve locale code
     *
     * @return string
     */
    public function getLocale()
    {
        return substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
    }
}
