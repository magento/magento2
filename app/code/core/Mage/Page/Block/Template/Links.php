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
 * Simple links list block
 *
 * @category   Mage
 * @package    Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Page_Block_Template_Links extends Mage_Core_Block_Template
{
    /**
     * All links
     *
     * @var array
     */
    protected $_links = array();

    /**
     * Cache key info
     *
     * @var null|array
     */
    protected $_cacheKeyInfo = null;

    protected $_template = 'template/links.phtml';

    /**
     * Get all links
     *
     * @return array
     */
    public function getLinks()
    {
        return $this->_links;
    }

    /**
     * Add link to the list
     *
     * @param string $label
     * @param string $url
     * @param string $title
     * @param boolean $prepare
     * @param array $urlParams
     * @param int $position
     * @param string|array $liParams
     * @param string|array $aParams
     * @param string $beforeText
     * @param string $afterText
     * @return Mage_Page_Block_Template_Links
     */
    public function addLink($label, $url = '', $title = '', $prepare = true, $urlParams = array(),
        $position = null, $liParams = null, $aParams = null, $beforeText = '', $afterText = ''
    ) {
        if ($label === null || false === $label) {
            return $this;
        }
        $link = new Varien_Object(array(
            'label'       => $label,
            'url'         => ($prepare ? $this->getUrl($url, (is_array($urlParams) ? $urlParams : array())) : $url),
            'title'       => $title,
            'li_params'   => $this->_prepareParams($liParams),
            'a_params'    => $this->_prepareParams($aParams),
            'before_text' => $beforeText,
            'after_text'  => $afterText,
        ));

        $this->_links[$this->_getNewPosition($position)] = $link;
        if (intval($position) > 0) {
            ksort($this->_links);
        }

        return $this;
    }

    /**
     * Add block to link list
     *
     * @param string $blockName
     * @return Mage_Page_Block_Template_Links
     */
    public function addLinkBlock($blockName)
    {
        $block = $this->getLayout()->getBlock($blockName);
        if ($block) {
            $this->_links[$this->_getNewPosition((int)$block->getPosition())] = $block;
            ksort($this->_links);
        }
        return $this;
    }

    /**
     * Remove Link block by blockName
     *
     * @param string $blockName
     * @return Mage_Page_Block_Template_Links
     */
    public function removeLinkBlock($blockName)
    {
        foreach ($this->_links as $key => $link) {
            if ($link instanceof Mage_Core_Block_Abstract && $link->getNameInLayout() == $blockName) {
                unset($this->_links[$key]);
            }
        }
        return $this;
    }

    /**
     * Removes link by url
     *
     * @param string $url
     * @return Mage_Page_Block_Template_Links
     */
    public function removeLinkByUrl($url)
    {
        foreach ($this->_links as $k => $v) {
            if ($v->getUrl() == $url) {
                unset($this->_links[$k]);
            }
        }

        return $this;
    }

    /**
     * Get cache key informative items
     * Provide string array key to share specific info item with FPC placeholder
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        if ($this->_cacheKeyInfo === null) {
            $links = array();
            if (!empty($this->_links)) {
                foreach ($this->_links as $position => $link) {
                    if ($link instanceof Varien_Object) {
                        $links[$position] = $link->getData();
                    }
                }
            }
            $this->_cacheKeyInfo = parent::getCacheKeyInfo() + array(
                'links' => base64_encode(serialize($links)),
                'name' => $this->getNameInLayout()
            );
        }

        return $this->_cacheKeyInfo;
    }

    /**
     * Prepare tag attributes
     *
     * @param string|array $params
     * @return string
     */
    protected function _prepareParams($params)
    {
        if (is_string($params)) {
            return $params;
        } elseif (is_array($params)) {
            $result = '';
            foreach ($params as $key=>$value) {
                $result .= ' ' . $key . '="' . addslashes($value) . '"';
            }
            return $result;
        }
        return '';
    }

    /**
     * Set first/last
     *
     * @return Mage_Page_Block_Template_Links
     */
    protected function _beforeToHtml()
    {
        if (!empty($this->_links)) {
            reset($this->_links);
            $this->_links[key($this->_links)]->setIsFirst(true);
            end($this->_links);
            $this->_links[key($this->_links)]->setIsLast(true);
        }
        return parent::_beforeToHtml();
    }

    /**
     * Return new link position in list
     *
     * @param int $position
     * @return int
     */
    protected function _getNewPosition($position = 0)
    {
        if (intval($position) > 0) {
            while (isset($this->_links[$position])) {
                $position++;
            }
        } else {
            $position = 0;
            foreach ($this->_links as $k=>$v) {
                $position = $k;
            }
            $position += 10;
        }
        return $position;
    }

}
