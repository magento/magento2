<?php
/**
 * URL Rewrite Option Provider
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Model;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Redirect type OptionProvider class
 */
class OptionProvider implements OptionSourceInterface
{
    /**
     * Permanent redirect code
     */
    const PERMANENT = 301;

    /**
     * Temporary redirect code
     */
    const TEMPORARY = 302;

    /**
     * Retrieve redirect type options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->getOptions() as $value => $label) {
            $options[] = [
                'label' => $label,
                'value' => $value
            ];
        }
        return $options;
    }

    /**
     * Retrieve options for edit form
     *
     * @return array
     */
    public function getOptions(): array
    {
        return [
            0 => __('No'),
            self::TEMPORARY => __('Temporary (302)'),
            self::PERMANENT => __('Permanent (301)')
        ];
    }
}
