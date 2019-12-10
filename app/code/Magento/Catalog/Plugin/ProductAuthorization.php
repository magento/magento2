<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Authorization;
use Magento\Framework\Exception\LocalizedException;

/**
 * Perform additional authorization for product operations.
 */
class ProductAuthorization
{
    /**
     * @var Authorization
     */
    private $authorization;

    /**
     * @param Authorization $authorization
     */
    public function __construct(Authorization $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * Authorize saving of a product.
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $product
     * @param bool $saveOptions
     * @throws LocalizedException
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        ProductRepositoryInterface $subject,
        ProductInterface $product,
        $saveOptions = false
    ): array {
        $this->authorization->authorizeSavingOf($product);

        return [$product, $saveOptions];
    }
}
