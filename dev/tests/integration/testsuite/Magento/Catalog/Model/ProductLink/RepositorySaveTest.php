<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ProductLink;

use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Model\ProductLink\Link;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppIsolation enabled
 */
class RepositorySaveTest extends TestCase
{
    /**
     * @var ProductLinkRepositoryInterface
     */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Bootstrap::getObjectManager()->get(ProductLinkRepositoryInterface::class);
    }

    public function testSaveWithNullLinkedProductSku(): void
    {
        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('The linked product SKU is invalid. Verify the data and try again.');

        $objectManager = Bootstrap::getObjectManager();
        /** @var Link $link */
        $link = $objectManager->create(Link::class);
        $link->setSku('sku1');
        $link->setLinkedProductSku(null);

        $this->repository->save($link);
    }

    public function testSaveWithEmptyLinkedProductSku(): void
    {
        $this->expectException(CouldNotSaveException::class);
        $this->expectExceptionMessage('The linked product SKU is invalid. Verify the data and try again.');

        $objectManager = Bootstrap::getObjectManager();
        /** @var Link $link */
        $link = $objectManager->create(Link::class);
        $link->setSku('sku1');
        $link->setLinkedProductSku('');

        $this->repository->save($link);
    }
}
