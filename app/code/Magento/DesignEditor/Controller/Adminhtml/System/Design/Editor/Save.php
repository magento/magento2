<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $response = ['error' => true, 'message' => __('Sorry, there was an unknown error.')];
        }

        /** @var $coreHelper \Magento\Core\Helper\Data */
        $coreHelper = $this->_objectManager->get('Magento\Core\Helper\Data');
        $this->getResponse()->representJson($coreHelper->jsonEncode($response));
    }
}
