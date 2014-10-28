<?php
/**
 * Product attribute edit form observer
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
namespace Magento\LayeredNavigation\Block\Adminhtml\Product\Attribute\Edit\Tab\Front;

use Magento\Backend\Model\Config\Source;
use Magento\Framework\Module\Manager;

class Observer
{
    /**
     * @var \Magento\Backend\Model\Config\Source\Yesno
     */
    protected $optionList;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @param Manager $moduleManager
     * @param Source\Yesno $optionList
     */
    public function __construct(Manager $moduleManager, Source\Yesno $optionList)
    {
        $this->optionList = $optionList;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param \Magento\Framework\Event $event
     * @return void
     */
    public function observe($event)
    {
        if (!$this->moduleManager->isOutputEnabled('Magento_LayeredNavigation')) {
            return;
        }

        /** @var \Magento\Framework\Data\Form\AbstractForm $form */
        $form = $event->getForm();

        $fieldset = $form->getElement('front_fieldset');

        $fieldset->addField(
            'is_filterable',
            'select',
            array(
                'name' => 'is_filterable',
                'label' => __("Use In Layered Navigation"),
                'title' => __('Can be used only with catalog input type Dropdown, Multiple Select and Price'),
                'note' => __('Can be used only with catalog input type Dropdown, Multiple Select and Price'),
                'values' => array(
                    array('value' => '0', 'label' => __('No')),
                    array('value' => '1', 'label' => __('Filterable (with results)')),
                    array('value' => '2', 'label' => __('Filterable (no results)')),
                ),
            )
        );

        $fieldset->addField(
            'is_filterable_in_search',
            'select',
            array(
                'name' => 'is_filterable_in_search',
                'label' => __("Use In Search Results Layered Navigation"),
                'title' => __('Can be used only with catalog input type Dropdown, Multiple Select and Price'),
                'note' => __('Can be used only with catalog input type Dropdown, Multiple Select and Price'),
                'values' => $this->optionList->toOptionArray(),
            )
        );

        $fieldset->addField(
            'position',
            'text',
            array(
                'name' => 'position',
                'label' => __('Position'),
                'title' => __('Position in Layered Navigation'),
                'note' => __('Position of attribute in layered navigation block'),
                'class' => 'validate-digits'
            )
        );
    }
}
