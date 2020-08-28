<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaGalleryUi\Ui\Component\Control;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\AuthorizationInterface;

/**
 * Upload Image button
 */
class UploadAssets implements ButtonProviderInterface
{
    private const ACL_UPLOAD_ASSETS= 'Magento_MediaGallery::upload_assets';

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * Constructor.
     *
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        AuthorizationInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        if (!$this->authorization->isAllowed(self::ACL_UPLOAD_ASSETS)) {
            return [];
        }

        return [
            'label' => __('Upload Image'),
            'disabled' => 'disabled',
            'on_click' => 'jQuery("#image-uploader-input").click();',
            'class' => 'action-default scalable add media-gallery-actions-buttons',
            'sort_order' => 20,
        ];
    }
}
