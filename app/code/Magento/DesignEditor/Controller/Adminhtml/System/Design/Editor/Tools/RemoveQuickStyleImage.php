<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor\Tools;

use Magento\Framework\Model\Exception as CoreException;

class RemoveQuickStyleImage extends \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor\Tools
{
    /**
     * Remove quick style image
     *
     * @return void
     */
    public function execute()
    {
        $fileName = $this->getRequest()->getParam('file_name', false);
        $elementName = $this->getRequest()->getParam('element', false);

        /** @var $uploaderModel \Magento\DesignEditor\Model\Editor\Tools\QuickStyles\ImageUploader */
        $uploaderModel = $this->_objectManager->get(
            'Magento\DesignEditor\Model\Editor\Tools\QuickStyles\ImageUploader'
        );
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $result = $uploaderModel->setTheme($editableTheme)->removeFile($fileName);

            /** @var $configFactory \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory */
            $configFactory = $this->_objectManager->create('Magento\DesignEditor\Model\Editor\Tools\Controls\Factory');

            $configuration = $configFactory->create(
                \Magento\DesignEditor\Model\Editor\Tools\Controls\Factory::TYPE_QUICK_STYLES,
                $editableTheme,
                $themeContext->getEditableTheme()->getParentTheme()
            );
            $configuration->saveData([$elementName => '']);

            $response = ['error' => false, 'content' => $result];
        } catch (CoreException $e) {
            $response = ['error' => true, 'message' => $e->getMessage()];
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        } catch (\Exception $e) {
            $errorMessage = __(
                'Something went wrong uploading the image.' .
                ' Please check the file format and try again (JPEG, GIF, or PNG).'
            );
            $response = ['error' => true, 'message' => $errorMessage];
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($response)
        );
    }
}
