<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Controller\Adminhtml\Directories;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface;
use Magento\MediaGalleryApi\Api\DeleteDirectoriesByPathsInterface;
use Psr\Log\LoggerInterface;

/**
 * Controller deleting the folders
 */
class Delete extends Action implements HttpPostActionInterface
{
    private const HTTP_OK = 200;
    private const HTTP_INTERNAL_ERROR = 500;
    private const HTTP_BAD_REQUEST = 400;

    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_MediaGalleryUiApi::delete_folder';

    /**
     * @var DeleteAssetsByPathsInterface
     */
    private $deleteAssetsByPaths;

    /**
     * @var DeleteDirectoriesByPathsInterface
     */
    private $deleteDirectoriesByPaths;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param DeleteAssetsByPathsInterface $deleteAssetsByPaths
     * @param DeleteDirectoriesByPathsInterface $deleteDirectoriesByPaths
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        DeleteAssetsByPathsInterface $deleteAssetsByPaths,
        DeleteDirectoriesByPathsInterface $deleteDirectoriesByPaths,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->deleteAssetsByPaths = $deleteAssetsByPaths;
        $this->deleteDirectoriesByPaths = $deleteDirectoriesByPaths;
        $this->logger = $logger;
    }

    /**
     * Delete folder by provided path.
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $path = $this->getRequest()->getParam('path');

        if (!$path) {
            $responseContent = [
                'success' => false,
                'message' => __('Folder path parameter is required.'),
            ];
            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData($responseContent);

            return $resultJson;
        }

        try {
            $this->deleteDirectoriesByPaths->execute([$path]);
            $this->deleteAssetsByPaths->execute([$path]);

            $responseCode = self::HTTP_OK;
            $responseContent = [
                'success' => true,
                'message' => __('You have successfully removed the folder.'),
            ];
        } catch (LocalizedException $exception) {
            $responseCode = self::HTTP_BAD_REQUEST;
            $responseContent = [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        } catch (Exception $exception) {
            $this->logger->critical($exception);
            $responseCode = self::HTTP_INTERNAL_ERROR;
            $responseContent = [
                'success' => false,
                'message' => __('An error occurred on attempt to remove folder.'),
            ];
        }

        $resultJson->setHttpResponseCode($responseCode);
        $resultJson->setData($responseContent);

        return $resultJson;
    }
}
