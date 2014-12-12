<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

class UploadJs extends \Magento\Theme\Controller\Adminhtml\System\Design\Theme
{
    /**
     * Upload js file
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function execute()
    {
        $themeId = $this->getRequest()->getParam('id');
        /** @var $serviceModel \Magento\Theme\Model\Uploader\Service */
        $serviceModel = $this->_objectManager->get('Magento\Theme\Model\Uploader\Service');
        /** @var $themeFactory \Magento\Framework\View\Design\Theme\FlyweightFactory */
        $themeFactory = $this->_objectManager->get('Magento\Framework\View\Design\Theme\FlyweightFactory');
        /** @var $jsService \Magento\Framework\View\Design\Theme\Customization\File\Js */
        $jsService = $this->_objectManager->get('Magento\Framework\View\Design\Theme\Customization\File\Js');
        try {
            $theme = $themeFactory->create($themeId);
            if (!$theme) {
                throw new \Magento\Framework\Model\Exception(__('We cannot find a theme with id "%1".', $themeId));
            }
            $jsFileData = $serviceModel->uploadJsFile('js_files_uploader');
            $jsFile = $jsService->create();
            $jsFile->setTheme($theme);
            $jsFile->setFileName($jsFileData['filename']);
            $jsFile->setData('content', $jsFileData['content']);
            $jsFile->save();

            /** @var $customization \Magento\Framework\View\Design\Theme\Customization */
            $customization = $this->_objectManager->create(
                'Magento\Framework\View\Design\Theme\CustomizationInterface',
                ['theme' => $theme]
            );
            $customJsFiles = $customization->getFilesByType(
                \Magento\Framework\View\Design\Theme\Customization\File\Js::TYPE
            );
            $result = ['error' => false, 'files' => $customization->generateFileInfo($customJsFiles)];
        } catch (\Magento\Framework\Model\Exception $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => __('We cannot upload the JS file.')];
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }
}
