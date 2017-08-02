<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Rss;

/**
 * Interface UrlBuilderInterface
 * @package Magento\Framework\App\Rss
 * @since 2.0.0
 */
interface UrlBuilderInterface
{
    /**
     * @param array $queryParams
     * @return mixed
     * @since 2.0.0
     */
    public function getUrl(array $queryParams = []);
}
