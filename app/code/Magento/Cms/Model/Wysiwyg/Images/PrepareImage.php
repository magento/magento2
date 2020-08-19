<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model\Wysiwyg\Images;

use Magento\Catalog\Helper\Data;
use Magento\Cms\Helper\Wysiwyg\Images as ImagesHelper;

class PrepareImage
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
     * @param array $data
     * @return string
     */
    public function execute(array $data): string
    {
        $filename = $this->imagesHelper->idDecode($data['filename']);
        $storeId = (int)$data['store_id'];

        $this->catalogHelper->setStoreId($storeId);
        $this->imagesHelper->setStoreId($storeId);

        if ($data['force_static_path']) {
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            $image = parse_url($this->imagesHelper->getCurrentUrl() . $filename, PHP_URL_PATH);
        } else {
            $image = $this->imagesHelper->getImageHtmlDeclaration($filename, $data['as_is']);
        }

        return $image;
    }
}
