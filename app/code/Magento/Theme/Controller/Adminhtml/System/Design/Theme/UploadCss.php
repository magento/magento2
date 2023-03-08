<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Controller\Adminhtml\System\Design\Theme;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Theme\Controller\Adminhtml\System\Design\Theme;
use Magento\Theme\Model\Uploader\Service;
use Psr\Log\LoggerInterface;

/**
 * Class UploadCss
 * @deprecated 100.2.0
 */
class UploadCss extends Theme
{
    /**
     * Upload css file
     *
     * @return void
     */
    public function execute()
    {
        /** @var Service $serviceModel */
        $serviceModel = $this->_objectManager->get(Service::class);
        try {
            $cssFileContent = $serviceModel->uploadCssFile('css_file_uploader');
            $result = ['error' => false, 'content' => $cssFileContent['content']];
        } catch (LocalizedException $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
        } catch (Exception $e) {
            $result = ['error' => true, 'message' => __('We can\'t upload the CSS file right now.')];
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(JsonHelper::class)->jsonEncode($result)
        );
    }
}
