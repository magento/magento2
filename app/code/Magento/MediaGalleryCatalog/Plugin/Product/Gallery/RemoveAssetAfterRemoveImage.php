<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryCatalog\Plugin\Product\Gallery;

use Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\Processor as ProcessorSubject;
use Psr\Log\LoggerInterface;

/**
 * Ensures that metadata is removed from the database when an image has been deleted (from legacy media gallery)
 */
class RemoveAssetAfterRemoveImage
{
    /**
     * @var DeleteAssetsByPathsInterface
     */
    private $deleteByPaths;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Processor constructor.
     *
     * @param DeleteAssetsByPathsInterface $deleteByPaths
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeleteAssetsByPathsInterface $deleteByPaths,
        LoggerInterface $logger
    ) {
        $this->deleteByPaths = $deleteByPaths;
        $this->logger = $logger;
    }

    /**
     * Remove media asset image after the product gallery image remove
     *
     * @param ProcessorSubject $subject
     * @param ProcessorSubject $result
     * @param Product $product
     * @param string $file
     * @return ProcessorSubject
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRemoveImage(
        ProcessorSubject $subject,
        ProcessorSubject $result,
        Product $product,
        $file
    ): ProcessorSubject {
        if (!is_string($file)) {
            return $result;
        }

        try {
            $this->deleteByPaths->execute([$file]);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $result;
    }
}
