<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model;

use Magento\Config\Model\Config\Source;
use Magento\Framework\Module\Manager;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Observer model
 */
class Observer
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesNo;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @param Manager $moduleManager
     * @param Source\Yesno $yesNo
     */
    public function __construct(Manager $moduleManager, Source\Yesno $yesNo)
    {
        $this->moduleManager = $moduleManager;
        $this->yesNo = $yesNo;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function addFieldsToAttribute(EventObserver $observer)
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

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function addSwatchAttributeType(EventObserver $observer)
    {
        if (!$this->moduleManager->isOutputEnabled('Magento_Swatches')) {
            return;
        }

        /** @var \Magento\Framework\DataObject $response */
        $response = $observer->getEvent()->getResponse();
        $types = $response->getTypes();
        $types[] = [
            'value' => \Magento\Swatches\Model\Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT,
            'label' => __('Visual Swatch'),
            'hide_fields' => [
                'is_unique',
                'is_required',
                'frontend_class',
                '_scope',
                '_default_value',
                '_front_fieldset',
            ],
        ];
        $types[] = [
            'value' => \Magento\Swatches\Model\Swatch::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT,
            'label' => __('Text Swatch'),
            'hide_fields' => [
                'is_unique',
                'is_required',
                'frontend_class',
                '_scope',
                '_default_value',
                '_front_fieldset',
            ],
        ];

        $response->setTypes($types);

        return $this;
    }
}
