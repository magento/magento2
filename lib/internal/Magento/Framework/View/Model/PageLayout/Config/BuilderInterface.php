<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
