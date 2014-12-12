<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
