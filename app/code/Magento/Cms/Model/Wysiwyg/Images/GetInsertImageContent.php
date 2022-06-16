<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Wysiwyg\Images;

use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Cms\Helper\Wysiwyg\Images as ImagesHelper;

class GetInsertImageContent
{
    /**
     * @var ImagesHelper
     */
    private $imagesHelper;

    /**
     * @var CatalogHelper
     */
    private $catalogHelper;

    /**
     * @param ImagesHelper $imagesHelper
     * @param CatalogHelper $catalogHelper
     */
    public function __construct(ImagesHelper $imagesHelper, CatalogHelper $catalogHelper)
    {
        $this->imagesHelper = $imagesHelper;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * Create a content (just a link or an html block) for inserting image to the content
     *
     * @param string $encodedFilename
     * @param bool $forceStaticPath
     * @param bool $renderAsTag
     * @param int|null $storeId
     * @return string
     */
    public function execute(
        string $encodedFilename,
        bool $forceStaticPath,
        bool $renderAsTag,
        ?int $storeId = null
    ): string {
        $filename = $this->imagesHelper->idDecode($encodedFilename);

        $this->catalogHelper->setStoreId($storeId);
        $this->imagesHelper->setStoreId($storeId);

        if ($forceStaticPath) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            return parse_url($this->imagesHelper->getCurrentUrl() . $filename, PHP_URL_PATH);
        }

        return $this->imagesHelper->getImageHtmlDeclaration($filename, $renderAsTag);
    }
}
