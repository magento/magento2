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

/**
 * Class \Magento\Captcha\Model\Config\Form\AbstractForm
 *
 * @since 2.0.0
 */
abstract class AbstractForm extends Value implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_configPath;

    /**
     * Returns options for form multiselect
     *
     * @return array
     * @since 2.0.0
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
