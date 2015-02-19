<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\Bundle;

interface ResolverInterface
{
    /**
     * @param \Magento\Framework\View\Asset\LocalInterface[] $assets
     * @return \Magento\Framework\View\Asset\LocalInterface[]
     */
    public function resolve($assets);

    /**
     * @param \Magento\Framework\View\Asset\LocalInterface[] $bundle
     * @return \Magento\Framework\View\Asset\LocalInterface[]
     */
    public function appendHtmlPart($bundle);
}