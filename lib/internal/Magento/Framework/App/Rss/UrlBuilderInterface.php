<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Rss;

/**
 * Interface UrlBuilderInterface
 * @package Magento\Framework\App\Rss
 */
interface UrlBuilderInterface
{
    /**
     * @param array $queryParams
     * @return mixed
     */
    public function getUrl(array $queryParams = []);
}
