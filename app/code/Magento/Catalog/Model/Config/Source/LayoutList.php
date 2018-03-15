<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Config\Source;

use Magento\Catalog\Model\Product\Attribute\Source\Layout;

/**
 * Returns layout list for Web>Default Layouts>Default Product Layout/Default Category Layout
 */
class LayoutList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Layout
     */
    private $layoutSource;

    /**
     * @param Layout $layoutSource
     */
    public function __construct(
        Layout $layoutSource
    ) {
        $this->layoutSource = $layoutSource;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->layoutSource->getAllOptions();
        }
        return $this->options;
    }
}
