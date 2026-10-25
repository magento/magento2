<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Model\PageLayout\Config;

/**
 * Interface Page Layout Config Builder
 *
 * @api
 */
interface BuilderInterface
{
    /**
     * @return \Magento\Framework\View\PageLayout\Config
     */
    public function getPageLayoutsConfig();
}
