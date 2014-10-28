<?php
/**
 *
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
namespace Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite;

class Edit extends \Magento\UrlRewrite\Controller\Adminhtml\Url\Rewrite
{
    /**#@+
     * Modes
     */
    const ID_MODE = 'id';
    const PRODUCT_MODE = 'product';
    const CATEGORY_MODE = 'category';
    const CMS_PAGE_MODE = 'cms_page';
    /**#@-*/

    /**
     * Get current mode
     *
     * @return string
     */
    protected function _getMode()
    {
        if ($this->_getProduct()->getId() || $this->getRequest()->has('product')) {
            $mode = self::PRODUCT_MODE;
        } elseif ($this->_getCategory()->getId() || $this->getRequest()->has('category')) {
            $mode = self::CATEGORY_MODE;
        } elseif ($this->_getCmsPage()->getId() || $this->getRequest()->has('cms_page')) {
            $mode = self::CMS_PAGE_MODE;
        } elseif ($this->getRequest()->has('id')) {
            $mode = self::ID_MODE;
        } else {
            $mode = $this->_objectManager->get('Magento\UrlRewrite\Block\Selector')->getDefaultMode();
        }
        return $mode;
    }

    /**
     * Show urlrewrite edit/create page
     *
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_UrlRewrite::urlrewrite');

        $mode = $this->_getMode();
        switch ($mode) {
            case self::PRODUCT_MODE:
                $editBlock = $this->_view->getLayout()->createBlock(
                    'Magento\UrlRewrite\Block\Catalog\Product\Edit',
                    '',
                    array(
                        'data' => array(
                            'category' => $this->_getCategory(),
                            'product' => $this->_getProduct(),
                            'is_category_mode' => $this->getRequest()->has('category'),
                            'url_rewrite' => $this->_getUrlRewrite()
                        )
                    )
                );
                break;
            case self::CATEGORY_MODE:
                $editBlock = $this->_view->getLayout()->createBlock(
                    'Magento\UrlRewrite\Block\Catalog\Category\Edit',
                    '',
                    array(
                        'data' => array('category' => $this->_getCategory(), 'url_rewrite' => $this->_getUrlRewrite())
                    )
                );
                break;
            case self::CMS_PAGE_MODE:
                $editBlock = $this->_view->getLayout()->createBlock(
                    'Magento\UrlRewrite\Block\Cms\Page\Edit',
                    '',
                    array(
                        'data' => array('cms_page' => $this->_getCmsPage(), 'url_rewrite' => $this->_getUrlRewrite())
                    )
                );
                break;
            case self::ID_MODE:
            default:
                $editBlock = $this->_view->getLayout()->createBlock(
                    'Magento\UrlRewrite\Block\Edit',
                    '',
                    array('data' => array('url_rewrite' => $this->_getUrlRewrite()))
                );
                break;
        }
        $this->_title->add($editBlock->getHeaderText());
        $this->_addContent($editBlock);
        $this->_view->renderLayout();
    }
}
