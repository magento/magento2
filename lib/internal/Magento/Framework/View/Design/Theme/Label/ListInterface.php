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
 * @since 2.0.0
 */
interface ListInterface
{
    /**
     * Return labels collection array
     *
     * @return array
     * @since 2.0.0
     */
    public function getLabels();
}
