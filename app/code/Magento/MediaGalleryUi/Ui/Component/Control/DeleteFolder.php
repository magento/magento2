<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaGalleryUi\Ui\Component\Control;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\AuthorizationInterface;

/**
 * Delete Folder button
 */
class DeleteFolder implements ButtonProviderInterface
{
    private const ACL_DELETE_FOLDER = 'Magento_MediaGalleryUiApi::delete_folder';

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
            'label' => __('Delete Folder'),
            'disabled' => 'disabled',
            'on_click' => 'jQuery("#delete_folder").trigger("delete_folder");',
            'class' => 'action-default scalable add media-gallery-actions-buttons',
            'sort_order' => 30,
        ];
        if (!$this->authorization->isAllowed(self::ACL_DELETE_FOLDER)) {
            $buttonData['disabled'] = 'disabled';
        }

        return $buttonData;
    }
}
