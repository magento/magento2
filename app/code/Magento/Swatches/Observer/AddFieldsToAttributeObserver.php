<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Observer;

use Magento\Config\Model\Config\Source;
use Magento\Framework\Module\ModuleManagerInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Observer model
 */
class AddFieldsToAttributeObserver implements ObserverInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesNo;

    /**
     * @var \Magento\Framework\Module\ModuleManagerInterface
     */
    protected $moduleManager;

    /**
     * @param ModuleManagerInterface $moduleManager
     * @param Source\Yesno $yesNo
     */
    public function __construct(ModuleManagerInterface $moduleManager, Source\Yesno $yesNo)
    {
        $this->moduleManager = $moduleManager;
        $this->yesNo = $yesNo;
    }

    /**
     * Execute.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->moduleManager->isOutputEnabled('Magento_Swatches')) {
            return;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $observer->getForm();
        $fieldset = $form->getElement('base_fieldset');
        $yesnoSource = $this->yesNo->toOptionArray();
        $fieldset->addField(
            'update_product_preview_image',
            'select',
            [
                'name' => 'update_product_preview_image',
                'label' => __('Update Product Preview Image'),
                'title' => __('Update Product Preview Image'),
                'note' => __('Filtering by this attribute will update the product image on catalog page'),
                'values' => $yesnoSource,
            ],
            'is_filterable'
        );
        $fieldset->addField(
            'use_product_image_for_swatch',
            'select',
            [
                'name' => 'use_product_image_for_swatch',
                'label' => __('Use Product Image for Swatch if Possible'),
                'title' => __('Use Product Image for Swatch if Possible'),
                'note' => __('Allows use fallback logic for replacing swatch image with product swatch or base image'),
                'values' => $yesnoSource
            ],
            'is_filterable'
        );
    }
}
