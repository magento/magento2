<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Model\PageLayout\Config;

/**
 * Interface BuilderInterface
 */
interface BuilderInterface
{
    /**
     * @return \Magento\Framework\View\PageLayout\Config
     */
    public function getPageLayoutsConfig();
}
