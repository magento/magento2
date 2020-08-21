<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Wysiwyg\Images;

use Magento\Catalog\Helper\Data;
use Magento\Cms\Helper\Wysiwyg\Images as ImagesHelper;

class GetInsertImageContent
{
    /**
     * @var ImagesHelper
     */
    private $imagesHelper;

    /**
     * @var Data
     */
    private $catalogHelper;

    /**
     * PrepareImage constructor.
     *
     * @param ImagesHelper $imagesHelper
     * @param Data $catalogHelper
     */
    public function __construct(
        ImagesHelper $imagesHelper,
        Data $catalogHelper
    ) {
        $this->imagesHelper = $imagesHelper;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * Prepare Image Contents for Insert
     *
     * @param string $encodedFilename
     * @param int $storeId
     * @param bool $forceStaticPath
     * @param bool $renderAsTag
     * @return string
     */
    public function execute(
        string $encodedFilename,
        int $storeId,
        bool $forceStaticPath,
        bool $renderAsTag
    ): string {
        $filename = $this->imagesHelper->idDecode($encodedFilename);

        $this->catalogHelper->setStoreId($storeId);
        $this->imagesHelper->setStoreId($storeId);

        if ($forceStaticPath) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            return parse_url(
                $this->imagesHelper->getCurrentUrl() . $filename,
                PHP_URL_PATH
            );
        }

        return $this->imagesHelper->getImageHtmlDeclaration($filename, $renderAsTag);
    }
}
