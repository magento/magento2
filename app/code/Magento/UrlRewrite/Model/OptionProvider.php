<?php
/**
 * URL Rewrite Option Provider
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\UrlRewrite\Model;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * @codeCoverageIgnore
 */
class OptionProvider implements OptionSourceInterface
{
    /**
     * Permanent redirect code
     */
    const PERMANENT = 301;

    /**
     * Redirect code
     */
    const TEMPORARY = 302;

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            0 => __('No'),
            self::TEMPORARY => __('Temporary (302)'),
            self::PERMANENT => __('Permanent (301)'),
        ];
    }
}
