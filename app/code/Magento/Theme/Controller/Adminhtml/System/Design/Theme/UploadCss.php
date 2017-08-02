<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

/**
 * Class UploadCss
 * @deprecated 2.2.0
 * @since 2.0.0
 */
class UploadCss extends \Magento\Theme\Controller\Adminhtml\System\Design\Theme
{
    /**
     * Upload css file
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        /** @var $serviceModel \Magento\Theme\Model\Uploader\Service */
        $serviceModel = $this->_objectManager->get(\Magento\Theme\Model\Uploader\Service::class);
        try {
            $cssFileContent = $serviceModel->uploadCssFile('css_file_uploader');
            $result = ['error' => false, 'content' => $cssFileContent['content']];
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => __('We can\'t upload the CSS file right now.')];
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }
}
