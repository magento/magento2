<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Controller\Adminhtml\Asset;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\MediaGalleryApi\Api\GetAssetsByIdsInterface;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Cms\Model\Wysiwyg\Images\Storage;

/**
 * Controller to get selected asset for ui-select component
 */
class GetSelected extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Cms::media_gallery';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var GetAssetsByIdsInterface
     */
    private $getAssetById;

    /**
     * @var Images
     */
    private $images;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @param JsonFactory $resultFactory
     * @param GetAssetsByIdsInterface $getAssetById
     * @param Context $context
     * @param Images $images
     * @param Storage $storage
     *
     */
    public function __construct(
        JsonFactory $resultFactory,
        GetAssetsByIdsInterface $getAssetById,
        Context $context,
        Images $images,
        Storage $storage
    ) {
        $this->resultJsonFactory = $resultFactory;
        $this->getAssetById = $getAssetById;
        $this->images = $images;
        $this->storage = $storage;
        parent::__construct($context);
    }

    /**
     * Return selected asset options.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $options = [];
        $assetIds = $this->getRequest()->getParam('ids');

        if (!is_array($assetIds)) {
            return $this->resultJsonFactory->create()->setData('parameter ids must be type of array');
        }
        $assets = $this->getAssetById->execute($assetIds);

        foreach ($assets as $asset) {
            $assetPath = $this->storage->getThumbnailUrl($this->images->getStorageRoot() . $asset->getPath());
            $options[] = [
                'value' => (string) $asset->getId(),
                'label' => $asset->getTitle(),
                'src' => $assetPath
            ];
        }

        return $this->resultJsonFactory->create()->setData($options);
    }
}
