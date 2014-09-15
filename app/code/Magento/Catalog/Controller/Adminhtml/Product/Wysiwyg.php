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
namespace Magento\Catalog\Controller\Adminhtml\Product;

class Wysiwyg extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * WYSIWYG editor action for ajax request
     *
     * @return void
     */
    public function execute()
    {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = $this->_objectManager->get(
            'Magento\Framework\StoreManagerInterface'
        )->getStore(
            $storeId
        )->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );

        $content = $this->_view->getLayout()->createBlock(
            'Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg\Content',
            '',
            array(
                'data' => array(
                    'editor_element_id' => $elementId,
                    'store_id' => $storeId,
                    'store_media_url' => $storeMediaUrl
                )
            )
        );

        $this->getResponse()->setBody($content->toHtml());
    }
}
