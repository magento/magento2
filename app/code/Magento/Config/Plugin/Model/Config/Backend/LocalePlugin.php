<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Config\Plugin\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Locale;
use Magento\Config\Model\Config\Source\Locale\Currency\All;
use Magento\Framework\Exception\LocalizedException;

class LocalePlugin
{
    /**
     * @var All
     */
    private $currencyList;

    /**
     * @param All $currencyList
     */
    public function __construct(
        All $currencyList
    ) {
        $this->currencyList = $currencyList;
    }

    /**
     * Check whether currency code value is acceptable or not
     *
     * @param Locale $subject
     * @return void
     */
    public function beforeSave(Locale $subject): void
    {
        if ($subject->isValueChanged()) {
            $values = $subject->getValue();
            if (count(array_diff($values, $this->getOptions()))) {
                throw new LocalizedException(__('There was an error save new configuration value.'));
            }
        }
    }

    /**
     * Get available options for weight unit
     *
     * @return array
     */
    private function getOptions()
    {
        $options = $this->currencyList->toOptionArray();

        return array_column($options, 'value');
    }
}
