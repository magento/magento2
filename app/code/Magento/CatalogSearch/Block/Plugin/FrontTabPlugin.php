<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Block\Plugin;

use Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front;
use Magento\CatalogSearch\Model\Source\Weight;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Fieldset;

class FrontTabPlugin
{
    /**
     * @var Weight
     */
    private $weightSource;

    /**
     * @param Weight $weightSource
     */
    public function __construct(Weight $weightSource)
    {
        $this->weightSource = $weightSource;
    }

    /**
     * @param Front $subject
     * @param callable $proceed
     * @param Form $form
     * @return Front
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSetForm(Front $subject, \Closure $proceed, Form $form)
    {
        $block = $proceed($form);
        /** @var Fieldset $fieldset */
        $fieldset = $form->getElement('front_fieldset');
        $fieldset->addField(
            'search_weight',
            'select',
            [
                'name' => 'search_weight',
                'label' => __('Search Weight'),
                'values' => $this->weightSource->getOptions()
            ],
            'is_searchable'
        );

        $subject->getChildBlock('form_after')
            ->addFieldMap(
                'search_weight',
                'search_weight'
            )
            ->addFieldDependence(
                'search_weight',
                'searchable',
                '1'
            );
        return $block;
    }
}
