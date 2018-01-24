<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

/**
 * Class UploadJs
 * @deprecated
 */
class UploadJs extends \Magento\Theme\Controller\Adminhtml\System\Design\Theme
{
    /**
     * Upload js file
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $themeId = $this->getRequest()->getParam('id');
        /** @var $serviceModel \Magento\Theme\Model\Uploader\Service */
        $serviceModel = $this->_objectManager->get(\Magento\Theme\Model\Uploader\Service::class);
        /** @var $themeFactory \Magento\Framework\View\Design\Theme\FlyweightFactory */
        $themeFactory = $this->_objectManager->get(\Magento\Framework\View\Design\Theme\FlyweightFactory::class);
        /** @var $jsService \Magento\Framework\View\Design\Theme\Customization\File\Js */
        $jsService = $this->_objectManager->get(\Magento\Framework\View\Design\Theme\Customization\File\Js::class);
        try {
            $theme = $themeFactory->create($themeId);
            if (!$theme) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We cannot find a theme with id "%1".', $themeId)
                );
            }
            $jsFileData = $serviceModel->uploadJsFile('js_files_uploader');
            $jsFile = $jsService->create();
            $jsFile->setTheme($theme);
            $jsFile->setFileName($jsFileData['filename']);
            $jsFile->setData('content', $jsFileData['content']);
            $jsFile->save();

            /** @var $customization \Magento\Framework\View\Design\Theme\Customization */
            $customization = $this->_objectManager->create(
                \Magento\Framework\View\Design\Theme\CustomizationInterface::class,
                ['theme' => $theme]
            );
            $customJsFiles = $customization->getFilesByType(
                \Magento\Framework\View\Design\Theme\Customization\File\Js::TYPE
            );
            $result = ['error' => false, 'files' => $customization->generateFileInfo($customJsFiles)];
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => __('We can\'t upload the JS file right now.')];
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }
}
