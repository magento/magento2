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
    private const ACL_UPLOAD_ASSETS= 'Magento_MediaGalleryUiApi::upload_assets';

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
     * @inheritdoc
     */
    public function getButtonData(): array
    {
        $buttonData = [
            'label' => __('Upload Image'),
            'on_click' => 'jQuery("#image-uploader-input").click();',
            'class' => 'action-default scalable add media-gallery-actions-buttons',
            'sort_order' => 20,
        ];

        if (!$this->authorization->isAllowed(self::ACL_UPLOAD_ASSETS)) {
            $buttonData['disabled'] = 'disabled';
        }

        return $buttonData;
    }
}
