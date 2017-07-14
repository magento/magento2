<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model\Product;

use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class BaseUrlRewriteGenerator
{
    /** @var UrlFinderInterface */
    protected $urlFinder;

    /**
     * @param array $paths
     * @param integer $entityId
     * @param integer $storeId
     * @return bool|mixed
     */
    protected function checkRequestPaths($paths, $entityId, $storeId)
    {
        $data = [];
        $urlRewrites = $this->urlFinder->findAllByData(
            [
                UrlRewrite::STORE_ID => $storeId,
                UrlRewrite::REQUEST_PATH => $paths
            ]
        );

        if ($urlRewrites) {
            foreach ($urlRewrites as $urlRewrite) {
                if ($urlRewrite->getEntityId() != $entityId) {
                    $data[] = $urlRewrite->getRequestPath();
                }
            }
        }

        $paths = array_diff($paths, $data);
        if (empty($paths)) {
            return false;
        }
        reset($paths);

        return current($paths);
    }
}
