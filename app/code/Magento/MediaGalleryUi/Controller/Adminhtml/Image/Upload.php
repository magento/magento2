<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Controller\Adminhtml\Image;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryUi\Model\UploadImage;
use Psr\Log\LoggerInterface;

/**
 * Controller responsible to upload the media gallery content
 */
class Upload extends Action implements HttpPostActionInterface
{
    private const HTTP_OK = 200;
    private const HTTP_BAD_REQUEST = 400;

    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_Cms::media_gallery';

    /**
     * @var UploadImage
     */
    private $uploadImage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param UploadImage $upload
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        UploadImage $upload,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->uploadImage = $upload;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $targetFolder = $this->getRequest()->getParam('target_folder');
        $type = $this->getRequest()->getParam('type');

        if (!$targetFolder) {
            $responseContent = [
                'success' => false,
                'message' => __('The target_folder parameter is required.'),
            ];
            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData($responseContent);

            return $resultJson;
        }

        try {
            $this->uploadImage->execute($targetFolder, $type);

            $responseCode = self::HTTP_OK;
            $responseContent = [
                'success' => true,
                'message' => __('The image was uploaded successfully.'),
            ];
        } catch (LocalizedException $exception) {
            $responseCode = self::HTTP_OK;
            $responseContent = [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        } catch (Exception $exception) {
            $this->logger->critical($exception);
            $responseCode = self::HTTP_OK;
            $responseContent = [
                'success' => false,
                'message' => __('Could not upload image.'),
            ];
        }

        $resultJson->setHttpResponseCode($responseCode);
        $resultJson->setData($responseContent);

        return $resultJson;
    }
}
