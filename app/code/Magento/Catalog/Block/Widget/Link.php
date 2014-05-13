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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Widget to display catalog link
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Widget;

class Link extends \Magento\Framework\View\Element\Html\Link implements \Magento\Widget\Block\BlockInterface
{
    /**
     * Entity model name which must be used to retrieve entity specific data.
     * @var null|\Magento\Catalog\Model\Resource\AbstractResource
     */
    protected $_entityResource = null;

    /**
     * Prepared href attribute
     *
     * @var string
     */
    protected $_href;

    /**
     * Prepared anchor text
     *
     * @var string
     */
    protected $_anchorText;

    /**
     * Url rewrite
     *
     * @var \Magento\UrlRewrite\Model\Resource\UrlRewrite
     */
    protected $_urlRewrite;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\UrlRewrite\Model\Resource\UrlRewrite $urlRewrite
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\UrlRewrite\Model\Resource\UrlRewrite $urlRewrite,
        array $data = array()
    ) {
        $this->_urlRewrite = $urlRewrite;
        parent::__construct($context, $data);
    }

    /**
     * Prepare url using passed id path and return it
     * or return false if path was not found in url rewrites.
     *
     * @return string|false
     */
    public function getHref()
    {
        if (!$this->_href) {

            if ($this->hasStoreId()) {
                $store = $this->_storeManager->getStore($this->getStoreId());
            } else {
                $store = $this->_storeManager->getStore();
            }

            /* @var $store \Magento\Store\Model\Store */
            $href = "";
            if ($this->getData('id_path')) {
                $href = $this->_urlRewrite->getRequestPathByIdPath($this->getData('id_path'), $store);
                if (!$href) {
                    return false;
                }
            }

            $this->_href = $store->getUrl('', array('_direct' => $href));
        }

        if (strpos($this->_href, "___store") === false) {
            $symbol = strpos($this->_href, "?") === false ? "?" : "&";
            $this->_href = $this->_href . $symbol . "___store=" . $store->getCode();
        }

        return $this->_href;
    }

    /**
     * Prepare label using passed text as parameter.
     * If anchor text was not specified get entity name from DB.
     *
     * @return string
     */
    public function getLabel()
    {
        if (!$this->_anchorText && $this->_entityResource) {
            if (!$this->getData('label')) {
                $idPath = explode('/', $this->_getData('id_path'));
                if (isset($idPath[1])) {
                    $id = $idPath[1];
                    if ($id) {
                        $this->_anchorText = $this->_entityResource->getAttributeRawValue(
                            $id,
                            'name',
                            $this->_storeManager->getStore()
                        );
                    }
                }
            } else {
                $this->_anchorText = $this->getData('label');
            }
        }

        return $this->_anchorText;
    }

    /**
     * Render block HTML
     * or return empty string if url can't be prepared
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->getHref()) {
            return parent::_toHtml();
        }
        return '';
    }
}
