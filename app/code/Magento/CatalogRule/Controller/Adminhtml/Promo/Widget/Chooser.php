<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Widget;

/**
 * Class \Magento\CatalogRule\Controller\Adminhtml\Promo\Widget\Chooser
 *
 */
class Chooser extends \Magento\CatalogRule\Controller\Adminhtml\Promo\Widget
{
    /**
     * Prepare block for chooser
     *
     * @return void
     */
    public function execute()
    {
        $request = $this->getRequest();

        switch ($request->getParam('attribute')) {
            case 'sku':
                $block = $this->_view->getLayout()->createBlock(
                    \Magento\CatalogRule\Block\Adminhtml\Promo\Widget\Chooser\Sku::class,
                    'promo_widget_chooser_sku',
                    ['data' => ['js_form_object' => $request->getParam('form')]]
                );
                break;

            case 'category_ids':
                $ids = $request->getParam('selected', []);
                if (is_array($ids)) {
                    foreach ($ids as $key => &$id) {
                        $id = (int)$id;
                        if ($id <= 0) {
                            unset($ids[$key]);
                        }
                    }

                    $ids = array_unique($ids);
                } else {
                    $ids = [];
                }

                $block = $this->_view->getLayout()->createBlock(
                    \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree::class,
                    'promo_widget_chooser_category_ids',
                    ['data' => ['js_form_object' => $request->getParam('form')]]
                )->setCategoryIds(
                    $ids
                );
                break;

            default:
                $block = false;
                break;
        }

        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }
}
