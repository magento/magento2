<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Image details block
 *
 * @api
 */
class ImageDetails extends Template
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param Template\Context $context
     * @param AuthorizationInterface $authorization
     * @param Json $json
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        Template\Context $context,
        AuthorizationInterface $authorization,
        Json $json,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        $this->authorization = $authorization;
        $this->json = $json;
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    /**
     * Retrieve actions json
     *
     * @return string
     */
    public function getActionsJson(): string
    {
        $actions = [
            [
                'title' => __('Cancel'),
                'handler' => 'closeModal',
                'name' => 'cancel',
                'classes' => 'action-default scalable cancel action-quaternary'
            ]
        ];

        if ($this->authorization->isAllowed('Magento_MediaGalleryUiApi::delete_assets')) {
            $actions[] = [
                'title' => __('Delete Image'),
                'handler' => 'deleteImageAction',
                'name' => 'delete',
                'classes' => 'action-default scalable delete action-quaternary'
            ];
        }

        if ($this->authorization->isAllowed('Magento_MediaGalleryUiApi::edit_assets')) {
            $actions[] = [
                'title' => __('Edit Details'),
                'handler' => 'editImageAction',
                'name' => 'edit',
                'classes' => 'action-default scalable edit action-quaternary'
            ];
        }

        if ($this->authorization->isAllowed('Magento_MediaGalleryUiApi::insert_assets')) {
            $actions[] = [
                'title' => __('Add Image'),
                'handler' => 'addImage',
                'name' => 'add-image',
                'classes' => 'scalable action-primary add-image-action'
            ];
        }

        return $this->json->serialize($actions);
    }
}
