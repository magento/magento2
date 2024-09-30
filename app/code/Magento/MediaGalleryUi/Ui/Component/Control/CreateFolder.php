<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaGalleryUi\Ui\Component\Control;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\AuthorizationInterface;

/**
 * Create Folder button
 */
class CreateFolder implements ButtonProviderInterface
{
    private const ACL_CREATE_FOLDER = 'Magento_MediaGalleryUiApi::create_folder';

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
            'label' => __('Create Folder'),
            'on_click' => 'jQuery("#create_folder").trigger("create_folder");',
            'class' => 'action-default scalable add media-gallery-actions-buttons',
            'sort_order' => 10,
        ];

        if (!$this->authorization->isAllowed(self::ACL_CREATE_FOLDER)) {
            $buttonData['disabled'] = 'disabled';
        }

        return $buttonData;
    }
}
