<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor\Tools;

use Magento\Framework\Model\Exception as CoreException;

class UploadJs extends \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor\Tools
{
    /**
     * Upload js file
     *
     * @return void
     */
    public function execute()
    {
        /** @var $serviceModel \Magento\Theme\Model\Uploader\Service */
        $serviceModel = $this->_objectManager->get('Magento\Theme\Model\Uploader\Service');
        /** @var $jsService \Magento\Framework\View\Design\Theme\Customization\File\Js */
        $jsService = $this->_objectManager->create('Magento\Framework\View\Design\Theme\Customization\File\Js');
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $jsFileData = $serviceModel->uploadJsFile('js_files_uploader');
            $jsFile = $jsService->create();
            $jsFile->setTheme($editableTheme);
            $jsFile->setFileName($jsFileData['filename']);
            $jsFile->setData('content', $jsFileData['content']);
            $jsFile->save();
            $this->_forward('jsList');
            return;
        } catch (CoreException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        } catch (\Exception $e) {
            $response = ['error' => true, 'message' => __('We cannot upload the JS file.')];
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
        );
    }
}
