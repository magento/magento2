<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Plugin\Catalog\Block\Adminhtml\Product\Edit\Tab;

class Attributes
{
    /**
     * @param \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes $subject
     * @param \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes $result
     * @return \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes
     */
    public function afterSetForm(
        \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes $subject,
        \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes $result
    ) {
        $form = $subject->getForm();
        $field = $form->getElement('url_key');
        if ($field) {
            $field->setRenderer(
                $subject->getLayout()->createBlock('Magento\CatalogUrlRewrite\Block\UrlKeyRenderer')
            );
        }
        return $result;
    }
}
