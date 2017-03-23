<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Data source to fill "Forms" field
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Captcha\Model\Config\Form;

abstract class AbstractForm extends \Magento\Framework\App\Config\Value implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var string
     */
    protected $_configPath;

    /**
     * Returns options for form multiselect
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $backendConfig = $this->_config->getValue($this->_configPath, 'default');
        if ($backendConfig) {
            foreach ($backendConfig as $formName => $formConfig) {
                if (!empty($formConfig['label'])) {
                    $optionArray[] = ['label' => $formConfig['label'], 'value' => $formName];
                }
            }
        }
        return $optionArray;
    }
}
