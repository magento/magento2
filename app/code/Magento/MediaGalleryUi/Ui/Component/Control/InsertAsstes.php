<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaGalleryUi\Ui\Component\Control;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\AuthorizationInterface;

/**
 * Add selected button
 */
class InsertAsstes implements ButtonProviderInterface
{
    private const ACL_INSERT_ASSETS = 'Magento_MediaGalleryUiApi::insert_assets';

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
            'label' => __('Add Selected'),
            'on_click' => 'return false;',
            'class' => 'action-primary no-display media-gallery-add-selected',
            'sort_order' => 110,
        ];

        if (!$this->authorization->isAllowed(self::ACL_INSERT_ASSETS)) {
            $buttonData['disabled'] = 'disabled';
        }

        return $buttonData;
    }
}
