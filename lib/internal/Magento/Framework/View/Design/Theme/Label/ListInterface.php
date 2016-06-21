<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Label;

/**
 * Label list interface
 *
 * @api
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
