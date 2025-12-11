<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Rss;

/**
 * Interface UrlBuilderInterface
 *
 * @api
 */
interface UrlBuilderInterface
{
    /**
     * @param array $queryParams
     * @return mixed
     */
    public function getUrl(array $queryParams = []);
}
