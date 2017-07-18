<?php
/**
 * Google Optimizer Form Helper
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Helper;

use Magento\Framework\Data\Form as DataForm;
use Magento\GoogleOptimizer\Model\Code as ModelCode;

/**
 * @api
 */
class Form extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Prepare form
     *
     * @param DataForm $form
     * @param ModelCode|null $experimentCodeModel
     * @param string $formName
     * @return void
     */
    public function addGoogleoptimizerFields(DataForm $form, ModelCode $experimentCodeModel = null, $formName = '')
    {
        $fieldset = $form->addFieldset(
            'googleoptimizer_fields',
            ['legend' => __('Google Analytics Content Experiments Code')]
        );

        $fieldset->addField(
            'experiment_script',
            'textarea',
            [
                'name' => 'experiment_script',
                'label' => __('Experiment Code'),
                'value' => $experimentCodeModel ? $experimentCodeModel->getExperimentScript() : '',
                'class' => 'textarea googleoptimizer',
                'required' => false,
                'note' => __('Experiment code should be added to the original page only.'),
                'data-form-part' => $formName
            ]
        );

        $fieldset->addField(
            'code_id',
            'hidden',
            [
                'name' => 'code_id',
                'value' => $experimentCodeModel ? $experimentCodeModel->getCodeId() : '',
                'required' => false,
                'data-form-part' => $formName
            ]
        );

        $form->setFieldNameSuffix('google_experiment');
    }
}
