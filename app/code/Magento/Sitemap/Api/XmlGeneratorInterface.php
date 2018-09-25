<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Api;


use Magento\Sitemap\Api\Data\SitemapInterface;

interface XmlGeneratorInterface
{
    public function execute(SitemapInterface $sitemap): bool;
}