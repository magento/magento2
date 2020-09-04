<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelperData;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Json\Helper\Data as JsonHelperData;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Image details block
 *
 * @api
 */
class ImageDetailsStandalone extends Template
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
     * @param JsonHelperData|null $jsonHelper
     * @param DirectoryHelperData|null $directoryHelper
     */
    public function __construct(
        Template\Context $context,
        AuthorizationInterface $authorization,
        Json $json,
        array $data = [],
        ?JsonHelperData $jsonHelper = null,
        ?DirectoryHelperData $directoryHelper = null
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
        $standaloneActions = [
            [
                'title' => __('Cancel'),
                'handler' => 'closeModal',
                'name' => 'cancel',
                'classes' => 'action-default scalable cancel action-quaternary'
            ]
        ];

        if ($this->authorization->isAllowed('Magento_MediaGalleryUiApi::delete_assets')) {
            $standaloneActions[] = [
                'title' => __('Delete Image'),
                'handler' => 'deleteImageAction',
                'name' => 'delete',
                'classes' => 'action-default scalable delete action-quaternary'
            ];
        }

        if ($this->authorization->isAllowed('Magento_MediaGalleryUiApi::edit_assets')) {
            $standaloneActions[] = [
                'title' => __('Edit Details'),
                'handler' => 'editImageAction',
                'name' => 'edit',
                'classes' => 'action-default scalable edit action-quaternary'
            ];
        }

        return $this->json->serialize($standaloneActions);
    }
}
