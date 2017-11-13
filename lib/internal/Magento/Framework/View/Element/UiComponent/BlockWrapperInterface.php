<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface BlockWrapperInterface
 */
interface BlockWrapperInterface extends UiComponentInterface
{
    /**
     * Get wrapped block
     *
     * @return BlockInterface
     */
    public function getBlock();
}
