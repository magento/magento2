<?php
/**
 * Google Optimizer Form Helper
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_GoogleOptimizer_Helper_Form extends Mage_Core_Helper_Abstract
{
    /**
     * Prepare form
     *
     * @param Varien_Data_Form $form
     * @param Mage_GoogleOptimizer_Model_Code|null $experimentCodeModel
     */
    public function addGoogleoptimizerFields(
        Varien_Data_Form $form,
        Mage_GoogleOptimizer_Model_Code $experimentCodeModel = null
    ) {
        $fieldset = $form->addFieldset('googleoptimizer_fields', array(
            'legend' => $this->__('Google Analytics Content Experiments Code'),
        ));

        $fieldset->addField('experiment_script', 'textarea', array(
            'name' => 'experiment_script',
            'label' => $this->__('Experiment Code'),
            'value' => $experimentCodeModel ? $experimentCodeModel->getExperimentScript() : array(),
            'class' => 'textarea googleoptimizer',
            'required' => false,
            'note' => $this->__('Note: Experiment code should be added to the original page only.'),
        ));

        $fieldset->addField('code_id', 'hidden', array(
            'name' => 'code_id',
            'value' => $experimentCodeModel ? $experimentCodeModel->getCodeId() : '',
            'required' => false,
        ));

        $form->setFieldNameSuffix('google_experiment');
    }
}
