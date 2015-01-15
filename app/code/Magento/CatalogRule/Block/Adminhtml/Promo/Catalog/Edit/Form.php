<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * description
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit;

use Magento\Backend\Block\Widget\Form as WidgetForm;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('promo_catalog_form');
        $this->setTitle(__('Rule Information'));
    }

    /**
     * @return WidgetForm
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('catalog_rule/promo_catalog/save'),
                    'method' => 'post',
                ],
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
