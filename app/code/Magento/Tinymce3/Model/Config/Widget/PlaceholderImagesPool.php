<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Tinymce3\Model\Config\Widget;

/**
 * Class PlaceholderImages provide ability to override placeholder images for Widgets
 * @deprecated
 */
class PlaceholderImagesPool
{
    /**
     * @var array
     */
    private $widgetPlaceholders;

    /**
     * PlaceholderImages constructor.
     * @param array $widgetPlaceholders
     */
    public function __construct(
        array $widgetPlaceholders = []
    ) {
        $this->widgetPlaceholders = $widgetPlaceholders;
    }

    /**
     * @return array
     */
    public function getWidgetPlaceholders() : array
    {
        return $this->widgetPlaceholders;
    }
}
