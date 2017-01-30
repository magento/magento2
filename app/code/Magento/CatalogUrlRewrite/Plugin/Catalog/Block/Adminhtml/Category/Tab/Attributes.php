<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Plugin\Catalog\Block\Adminhtml\Category\Tab;

class Attributes
{
    /**
     * @param \Magento\Catalog\Block\Adminhtml\Category\Tab\Attributes $subject
     * @param \Magento\Catalog\Block\Adminhtml\Category\Tab\Attributes $result
     *
     * @return \Magento\Catalog\Block\Adminhtml\Category\Tab\Attributes
     */
    public function afterSetForm(
        \Magento\Catalog\Block\Adminhtml\Category\Tab\Attributes $subject,
        \Magento\Catalog\Block\Adminhtml\Category\Tab\Attributes $result
    ) {
        $form = $subject->getForm();
        $fieldset = $form->getElements()[0];
        $field = $form->getElement('url_key');
        if ($field) {
            if ($subject->getCategory()->getLevel() == 1) {
                $fieldset->removeField('url_key');
                $fieldset->addField(
                    'url_key',
                    'hidden',
                    ['name' => 'url_key', 'value' => $subject->getCategory()->getUrlKey()]
                );
            } else {
                $field->setRenderer(
                    $subject->getLayout()->createBlock('Magento\CatalogUrlRewrite\Block\UrlKeyRenderer')
                );
            }
        }
        return $result;
    }
}
