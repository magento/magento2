<?php
/**
 * Product attribute edit form observer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\LayeredNavigation\Observer\Edit\Tab\Front;

use Magento\Config\Model\Config\Source;
use Magento\Framework\Module\Manager;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\LayeredNavigation\Observer\Edit\Tab\Front\ProductAttributeFormBuildFrontTabObserver
 *
 * @since 2.0.0
 */
class ProductAttributeFormBuildFrontTabObserver implements ObserverInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     * @since 2.0.0
     */
    protected $optionList;

    /**
     * @var \Magento\Framework\Module\Manager
     * @since 2.0.0
     */
    protected $moduleManager;

    /**
     * @param Manager $moduleManager
     * @param Source\Yesno $optionList
     * @since 2.0.0
     */
    public function __construct(Manager $moduleManager, Source\Yesno $optionList)
    {
        $this->optionList = $optionList;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->moduleManager->isOutputEnabled('Magento_LayeredNavigation')) {
            return;
        }

        /** @var \Magento\Framework\Data\Form\AbstractForm $form */
        $form = $observer->getForm();

        $fieldset = $form->getElement('front_fieldset');

        $fieldset->addField(
            'is_filterable',
            'select',
            [
                'name' => 'is_filterable',
                'label' => __("Use in Layered Navigation"),
                'title' => __('Can be used only with catalog input type Dropdown, Multiple Select and Price'),
                'note' => __('Can be used only with catalog input type Dropdown, Multiple Select and Price.'),
                'values' => [
                    ['value' => '0', 'label' => __('No')],
                    ['value' => '1', 'label' => __('Filterable (with results)')],
                    ['value' => '2', 'label' => __('Filterable (no results)')],
                ],
            ]
        );

        $fieldset->addField(
            'is_filterable_in_search',
            'select',
            [
                'name' => 'is_filterable_in_search',
                'label' => __("Use in Search Results Layered Navigation"),
                'title' => __('Can be used only with catalog input type Dropdown, Multiple Select and Price'),
                'note' => __('Can be used only with catalog input type Dropdown, Multiple Select and Price.'),
                'values' => $this->optionList->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'position',
            'text',
            [
                'name' => 'position',
                'label' => __('Position'),
                'title' => __('Position in Layered Navigation'),
                'note' => __('Position of attribute in layered navigation block.'),
                'class' => 'validate-digits'
            ]
        );
    }
}
