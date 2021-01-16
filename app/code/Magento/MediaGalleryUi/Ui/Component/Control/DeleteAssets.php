<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaGalleryUi\Ui\Component\Control;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\AuthorizationInterface;

/**
 * Delete images button
 */
class DeleteAssets implements ButtonProviderInterface
{
    private const ACL_DELETE_ASSETS= 'Magento_MediaGalleryUiApi::delete_assets';

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
            'label' => __('Delete Images...'),
            'on_click' => 'jQuery(window).trigger("massAction.MediaGallery")',
            'class' => 'action-default scalable add media-gallery-actions-buttons',
            'sort_order' => 50,
        ];

        if (!$this->authorization->isAllowed(self::ACL_DELETE_ASSETS)) {
            $buttonData['disabled'] = 'disabled';
        }

        return $buttonData;
    }
}
