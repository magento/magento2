<?php
/**
 * URL Rewrite Option Provider
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\UrlRewrite\Model;

use Magento\Framework\Option\ArrayInterface;

/**
 * @codeCoverageIgnore
 */
class OptionProvider implements ArrayInterface
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
