<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Config\Structure\Element;

use Magento\Config\Model\Config\Structure\Element\Field as FieldConfigStructure;
use Magento\Paypal\Model\Config\StructurePlugin as ConfigStructurePlugin;

/**
 * Plugin for \Magento\Config\Model\Config\Structure\Element\Field
 */
class FieldPlugin
{
    /**
     * Get original configPath (not changed by PayPal configuration inheritance)
     *
     * @param FieldConfigStructure $subject
     * @param string|null $result
     * @return string|null
     */
    public function afterGetConfigPath(FieldConfigStructure $subject, $result)
    {
        if (!$result && strpos($subject->getPath(), 'payment_') === 0) {
            $result = preg_replace(
                '@^(' . implode('|', ConfigStructurePlugin::getPaypalConfigCountries(true)) . ')/@',
                'payment/',
                $subject->getPath()
            );
        }

        return $result;
    }
}
