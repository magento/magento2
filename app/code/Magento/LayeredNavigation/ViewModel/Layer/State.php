<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */

namespace Magento\LayeredNavigation\ViewModel\Layer;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\LayeredNavigation\Block\Navigation\State as StateBlock;

class State implements ArgumentInterface
{
    /**
     * @param StateBlock $block
     */
    public function __construct(private StateBlock $block)
    {
    }

    /**
     * Skip string value '0' to be filtered against stripTags
     *
     * @param string $data
     * @return string
     */
    public function stripTags(string $data): string
    {
        if ($data !== '0') {
            return $this->block->stripTags($data);
        }

        return $data;
    }
}
