<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Model\PageLayout\Config;

/**
 * Interface BuilderInterface
 * @since 2.0.0
 */
interface BuilderInterface
{
    /**
     * @return \Magento\Framework\View\PageLayout\Config
     * @since 2.0.0
     */
    public function getPageLayoutsConfig();
}
