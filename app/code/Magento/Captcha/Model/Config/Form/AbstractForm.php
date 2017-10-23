<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Data source to fill "Forms" field
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Captcha\Model\Config\Form;

use Magento\Framework\App\Config\Value;

abstract class AbstractForm extends Value implements \Magento\Framework\Option\ArrayInterface
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
