<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

use Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Design\Theme\Customization;
use Magento\Framework\View\Design\Theme\Customization\File\Js as CustomizationFileJs;
use Magento\Framework\View\Design\Theme\CustomizationInterface;
use Magento\Framework\View\Design\Theme\FlyweightFactory;
use Magento\Theme\Controller\Adminhtml\System\Design\Theme;
use Magento\Theme\Model\Uploader\Service;
use Psr\Log\LoggerInterface;

/**
 * Class UploadJs
 * @deprecated 101.0.0
 */
class UploadJs extends Theme implements HttpGetActionInterface
{
    /**
     * Upload js file
     *
     * @return void
     */
    public function execute()
    {
        $themeId = $this->getRequest()->getParam('id');
        /** @var Service $serviceModel */
        $serviceModel = $this->_objectManager->get(Service::class);
        /** @var FlyweightFactory $themeFactory */
        $themeFactory = $this->_objectManager->get(FlyweightFactory::class);
        /** @var CustomizationFileJs $jsService */
        $jsService = $this->_objectManager->get(CustomizationFileJs::class);
        try {
            $theme = $themeFactory->create($themeId);
            if (!$theme) {
                throw new LocalizedException(
                    __('We cannot find a theme with id "%1".', $themeId)
                );
            }
            $jsFileData = $serviceModel->uploadJsFile('js_files_uploader');
            $jsFile = $jsService->create();
            $jsFile->setTheme($theme);
            $jsFile->setFileName($jsFileData['filename']);
            $jsFile->setData('content', $jsFileData['content']);
            $jsFile->save();

            /** @var Customization $customization */
            $customization = $this->_objectManager->create(
                CustomizationInterface::class,
                ['theme' => $theme]
            );
            $customJsFiles = $customization->getFilesByType(
                CustomizationFileJs::TYPE
            );
            $result = ['error' => false, 'files' => $customization->generateFileInfo($customJsFiles)];
        } catch (LocalizedException $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
        } catch (Exception $e) {
            $result = ['error' => true, 'message' => __('We can\'t upload the JS file right now.')];
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(JsonHelper::class)->jsonEncode($result)
        );
    }
}
