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
use Magento\Framework\Filter\Input\MaliciousCode;

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
    public const ADMIN_RESOURCE = 'Magento_MediaGalleryUiApi::upload_assets';

    /**
     * @var UploadImage
     */
    private $uploadImage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MaliciousCode
     */
    private $filter;

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
        $this->filter = new MaliciousCode();
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $param_path = $this->getRequest()->getParam('target_folder');

        if (!$param_path) {
            $responseContent = [
                'success' => false,
                'message' => __('The target_folder parameter is required.'),
            ];
            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData($responseContent);
            return $resultJson;
        }

        $customExpression = ['/[.]{1}/'];
        $this->filter->setExpressions($customExpression);
        $targetFolder = $this->filter->filter($param_path);

        if ($this->canonicalizePath($targetFolder,null) == 0) {
            $responseCode = self::HTTP_BAD_REQUEST;
            $responseContent = [
                'success' => false,
                'message' => __('Could not upload image.'),
            ];
        } else {
            $type = $this->getRequest()->getParam('type');

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
        }

        $resultJson->setHttpResponseCode($responseCode);
        $resultJson->setData($responseContent);
        return $resultJson;
    }

    function canonicalizePath($path, $cwd=null): int
    {
        if (substr($path, 0, 1) === "/") {
            $filename = $path;
        } else {
            $root      = is_null($cwd) ? getcwd() : $cwd;
            $filename  = sprintf("%s/%s", $root, $path);
        }

        $dirname   = dirname($filename);
        $canonical = realpath($dirname);

        if ($canonical === false || $canonical === "/") {
            return 0;
        } else {
            return 1;
        }
    }
}
