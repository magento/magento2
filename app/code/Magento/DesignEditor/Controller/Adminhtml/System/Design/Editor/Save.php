<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor;

class Save extends \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor
{
    /**
     * Apply changes from 'staging' theme to 'virtual' theme
     *
     * @return void
     */
    public function execute()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id');

        /** @var \Magento\DesignEditor\Model\Theme\Context $themeContext */
        $themeContext = $this->_objectManager->get('Magento\DesignEditor\Model\Theme\Context');
        $themeContext->setEditableThemeById($themeId);
        try {
            $themeContext->copyChanges();
            if ($this->_customizationConfig->isThemeAssignedToStore($themeContext->getEditableTheme())) {
                $message = __('You updated your live store.');
            } else {
                $message = __('You saved updates to this theme.');
            }
            $response = ['message' => $message];
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $response = ['error' => true, 'message' => __('Sorry, something went wrong. That\'s all we know.')];
        }

        /** @var $jsonHelper \Magento\Framework\Json\Helper\Data */
        $jsonHelper = $this->_objectManager->get('Magento\Framework\Json\Helper\Data');
        $this->getResponse()->representJson($jsonHelper->jsonEncode($response));
    }
}
