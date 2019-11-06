<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestFramework\Catalog\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\LayoutUpdateManager;

/**
 * Easy way to fake available files.
 */
class ProductLayoutUpdateManager extends LayoutUpdateManager
{
    /**
     * @var array Keys are product IDs, values - file names.
     */
    private $fakeFiles = [];

    /**
     * Supply fake files for a product.
     *
     * @param int $forProductId
     * @param string[]|null $files Pass null to reset.
     */
    public function setFakeFiles(int $forProductId, ?array $files): void
    {
        if ($files === null) {
            unset($this->fakeFiles[$forProductId]);
        } else {
            $this->fakeFiles[$forProductId] = $files;
        }
    }

    /**
     * @inheritDoc
     */
    public function fetchAvailableFiles(ProductInterface $product): array
    {
        if (array_key_exists($product->getId(), $this->fakeFiles)) {
            return $this->fakeFiles[$product->getId()];
        }

        return parent::fetchAvailableFiles($product);
    }
}
