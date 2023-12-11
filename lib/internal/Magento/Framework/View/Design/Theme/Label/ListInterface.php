<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Label;

/**
 * Label list interface
 *
 * @api
 * @since 100.0.2
 */
interface ListInterface
{
    /**
     * Return labels collection array
     *
     * @return array
     */
    public function getLabels();
}
