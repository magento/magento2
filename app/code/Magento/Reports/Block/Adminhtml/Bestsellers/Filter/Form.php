<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Block\Adminhtml\Bestsellers\Filter;

use Magento\Framework\Data\Form\Element\Fieldset;
use \Magento\Reports\Block\Adminhtml\Filter\Form as ReportsBlockFilterForm;

/**
 * Sales Adminhtml report bestseller filter form
 *
 * @package Magento\Reports\Block\Adminhtml\Bestsellers\Filter
 * @api
 */
class Form extends ReportsBlockFilterForm
{
    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        /** @var Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');
        if (is_object($fieldset) && $fieldset instanceof Fieldset) {
            $fieldset->addField(
                'rating_limit',
                'select',
                [
                    'name'    => 'rating_limit',
                    'label'   => __('Display items'),
                    'options' => array_combine($i = [5, 10, 20, 50, 100], $i),
                ],
                'to'
            );
        }

        return $this;
    }
}