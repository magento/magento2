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
     * Add CSS file to HEAD entity
     *
     * @param string $name
     * @param string $params
     * @param string|null $if
     * @param string|null $cond
     * @return Mage_Page_Block_Html_Head
     */
    public function addCss($name, $params = '', $if = null, $cond = null)
    {
        $params = 'rel="stylesheet" type="text/css"' . ($params ? ' ' . trim($params) : ' media="all"');
        return $this->_addItem('css', $name, $params, $if, $cond);
    }

    /**
     * Add JavaScript file to HEAD entity
     *
     * @param string $name
     * @param string $params
     * @param string|null $if
     * @param string|null $cond
     * @return Mage_Page_Block_Html_Head
     */
    public function addJs($name, $params = '', $if = null, $cond = null)
    {
        return $this->_addItem('js', $name, $params, $if, $cond);
    }

    /**
     * Add CSS file for Internet Explorer only to HEAD entity
     *
     * @param string $name
     * @param string $params
     * @param string|null $cond
     * @return Mage_Page_Block_Html_Head
     */
    public function addCssIe($name, $params = '', $cond = null)
    {
        return $this->addCss($name, $params, 'IE', $cond);
    }

    /**
     * Add JavaScript file for Internet Explorer only to HEAD entity
     *
     * @param string $name
     * @param string $params
     * @param string|null $cond
     * @return Mage_Page_Block_Html_Head
     */
    public function addJsIe($name, $params = '', $cond = null)
    {
        return $this->addJs($name, $params, 'IE', $cond);
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
        return $this->_addItem('link', $href, 'rel="alternate" type="application/rss+xml" title="' . $title . '"');
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
        return $this->_addItem('link', $href, 'rel="' . $rel . '"');
    }

    /**
     * Add HEAD Item
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string|null $if
     * @param string|null $cond
     * @return Mage_Page_Block_Html_Head
     * @throws Magento_Exception
     */
    protected function _addItem($type, $name, $params = '', $if = null, $cond = null)
    {
        if (empty($name)) {
            throw new Magento_Exception('File name must be not empty.');
        }
        $this->_data['items'][$type . '/' . $name] = array(
            'type' => $type,
            'name' => $name,
            'params' => trim($params),
            'if' => $if,
            'cond' => $cond,
        );
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
        unset($this->_data['items'][$type . '/' . $name]);
        return $this;
    }

    /**
     * Render HTML for the added head items
     *
     * @return string
     */
    public function getCssJsHtml()
    {
        $lines = array();
        $meta = array();
        foreach ($this->_data['items'] as $item) {
            if (!is_null($item['cond']) && !$this->getData($item['cond'])) {
                continue;
            }
            $contentType = $item['type'];
            $group = $item['if'] . '|' . (empty($item['params']) ? '_' : $item['params']) . '|' . $contentType;
            $meta[$group] = array($item['if'], (string)$item['params'], $contentType);
            $lines[$group][] = $item['name'];
        }

        $html = '';
        foreach ($lines as $group => $items) {
            list($if, $params, $contentType) = $meta[$group];
            if (!empty($if)) {
                $html .= '<!--[if ' . $if . ']>' . "\n";
            }
            if ($params) {
                $params = ' ' . $params;
            }
            switch ($contentType) {
                case Mage_Core_Model_Design_Package::CONTENT_TYPE_CSS:
                    $html .= $this->_generateCssHtml($items, $params);
                    break;
                case Mage_Core_Model_Design_Package::CONTENT_TYPE_JS:
                    $html .= $this->_generateJsHtml($items, $params);
                    break;
                case 'link':
                    foreach ($items as $file) {
                        $html .= sprintf('<link%s href="%s" />' . "\n", $params, $file);
                    }
                    break;
                default:
                    break;
            }
            if (!empty($if)) {
                $html .= '<![endif]-->' . "\n";
            }
        }
        return $html;
    }

    /**
     * Generate css links
     *
     * @param array $items
     * @param array $params
     * @return string
     */
    protected function _generateCssHtml($items, $params)
    {
        $html = '';
        $pattern = '<link%s href="%s" />' . "\n";
        try {
            foreach (Mage::getDesign()->getOptimalCssUrls($items) as $url) {
                $html .= sprintf($pattern, $params, $url);
            }
        } catch (Magento_Exception $e) {
            $html .= sprintf($pattern, $params, $this->_getNotFoundUrl());
        }
        return $html;
    }

    /**
     * Generate js links
     *
     * @param array $items
     * @param array $params
     * @return string
     */
    protected function _generateJsHtml($items, $params)
    {
        $html = '';
        $pattern = '<script%s type="text/javascript" src="%s"></script>' . "\n";
        try {
            foreach (Mage::getDesign()->getOptimalJsUrls($items) as $url) {
                $html .= sprintf($pattern, $params, $url);
            }
        } catch (Magento_Exception $e) {
            $html .= sprintf($pattern, $params, $this->_getNotFoundUrl());
        }
        return $html;
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
     * @param string $title
     * @return Mage_Page_Block_Html_Head
     */
    public function setTitle($title)
    {
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
