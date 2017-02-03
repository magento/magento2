<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\GoogleOptimizer\Observer\Block\Category;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddGoogleExperimentFieldsObserver implements ObserverInterface
{
    /**
     * @var \Magento\GoogleOptimizer\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param \Magento\GoogleOptimizer\Helper\Data $dataHelper
     */
    public function __construct(
        \Magento\GoogleOptimizer\Helper\Data $dataHelper
    ) {
        $this->dataHelper = $dataHelper;
    }

    /**
     * Adds Google Experiment fields to category creation form on product edit page
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->dataHelper->isGoogleExperimentActive()) {
            $block = $observer->getEvent()->getBlock();
            if ($block->getForm() && $block->getForm()->getId() == 'new_category_form') {
                $fieldset = $block->getForm()->getElement('new_category_form_fieldset');
                $fieldset->addField(
                    'experiment_script',
                    'textarea',
                    [
                        'name' => 'google_experiment[experiment_script]',
                        'label' => __('Experiment Code'),
                        'value' => '',
                        'class' => 'textarea googleoptimizer',
                        'required' => false,
                        'note' => __('Experiment code should be added to the original page only.')
                    ]
                );

                $fieldset->addField(
                    'code_id',
                    'hidden',
                    [
                        'name' => 'google_experiment[code_id]',
                        'value' => '',
                        'required' => false
                    ]
                );
            }
        }
    }
}
