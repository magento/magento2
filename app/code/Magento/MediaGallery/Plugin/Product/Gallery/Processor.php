<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGallery\Plugin\Product\Gallery;

use Magento\MediaGalleryApi\Model\Asset\Command\DeleteByPathInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\Processor as ProcessorSubject;
use Psr\Log\LoggerInterface;

/**
 * Ensures that metadata is removed from the database when a product image has been deleted.
 */
class Processor
{
    /**
     * @var DeleteByPathInterface
     */
    private $deleteMediaAssetByPath;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Processor constructor.
     *
     * @param DeleteByPathInterface $deleteMediaAssetByPath
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeleteByPathInterface $deleteMediaAssetByPath,
        LoggerInterface $logger
    ) {
        $this->deleteMediaAssetByPath = $deleteMediaAssetByPath;
        $this->logger = $logger;
    }

    /**
     * Remove media asset image after the product gallery image remove
     *
     * @param ProcessorSubject $subject
     * @param ProcessorSubject $result
     * @param Product $product
     * @param string $file
     *
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
            $this->deleteMediaAssetByPath->execute($file);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }

        return $result;
    }
}
