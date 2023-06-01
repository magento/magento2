<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Cache\Product\MediaGallery;

use Magento\CatalogGraphQl\Model\Resolver\Cache\Product\ModelDehydrator;
use Magento\GraphQlResolverCache\Model\Resolver\Result\DehydratorInterface;

/**
 * MediaGallery resolver data dehydrator to create snapshot data necessary to restore model.
 */
class ProductModelDehydrator implements DehydratorInterface
{
    /**
     * @var ModelDehydrator
     */
    private $productModelDehydrator;

    /**
     * @param  $productModelDehydrator
     */
    public function __construct(
        ModelDehydrator $productModelDehydrator
    ) {
        $this->productModelDehydrator = $productModelDehydrator;
    }

    /**
     * @inheritdoc
     */
    public function dehydrate(array &$resolvedValue): void
    {
        if (count($resolvedValue) > 0) {
            $keys = array_keys($resolvedValue);
            $firstKey = array_pop($keys);
            $this->productModelDehydrator->dehydrate($resolvedValue[$firstKey]);
            foreach ($keys as $key) {
                $resolvedValue[$key]['model_info'] = $resolvedValue[$firstKey]['model_info'];
            }
        }
    }
}
