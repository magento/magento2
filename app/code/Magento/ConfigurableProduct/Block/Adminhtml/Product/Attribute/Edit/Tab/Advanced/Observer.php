<?php
/**
 * Product edit form observer
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
namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Attribute\Edit\Tab\Advanced;

use Magento\Backend\Model\Config\Source;

class Observer
{
    /**
     * @var \Magento\Backend\Model\Config\Source\Yesno
     */
    protected $optionList;

    /**
     * @param Source\Yesno $optionList
     */
    public function __construct(Source\Yesno $optionList)
    {
        $this->optionList = $optionList;
    }

    /**
     * @param \Magento\Framework\Event $event
     * @return void
     */
    public function observe($event)
    {
        /** @var \Magento\Framework\Data\Form\AbstractForm $form */
        $form = $event->getForm();
        /** @var  $fieldset */
        $fieldset = $form->getElement('advanced_fieldset');

        $fieldset->addField(
            'is_configurable',
            'select',
            array(
                'name' => 'is_configurable',
                'label' => __('Use To Create Configurable Product'),
                'values' => $this->optionList->toOptionArray()
            )
        );
    }
}
