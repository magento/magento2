<?php
/**
 * Google Optimizer Form Helper
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GoogleOptimizer\Helper;

use Magento\Framework\Data\Form as DataForm;
use Magento\GoogleOptimizer\Model\Code as ModelCode;

class Form extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Prepare form
     *
     * @param DataForm $form
     * @param Code|null $experimentCodeModel
     * @return void
     */
    public function addGoogleoptimizerFields(DataForm $form, ModelCode $experimentCodeModel = null)
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
                'note' => __('Note: Experiment code should be added to the original page only.')
            ]
        );

        $fieldset->addField(
            'code_id',
            'hidden',
            [
                'name' => 'code_id',
                'value' => $experimentCodeModel ? $experimentCodeModel->getCodeId() : '',
                'required' => false
            ]
        );

        $form->setFieldNameSuffix('google_experiment');
    }
}
