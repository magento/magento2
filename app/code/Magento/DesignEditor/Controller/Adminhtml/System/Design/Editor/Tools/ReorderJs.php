<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor\Tools;

use Magento\Framework\Model\Exception as CoreException;

class ReorderJs extends \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor\Tools
{
    /**
     * Reorder js file
     *
     * @return void
     */
    public function execute()
    {
        $reorderJsFiles = (array)$this->getRequest()->getParam('js_order', []);
        try {
            $themeContext = $this->_initContext();
            $editableTheme = $themeContext->getStagingTheme();
            $editableTheme->getCustomization()->reorder(
                \Magento\Framework\View\Design\Theme\Customization\File\Js::TYPE,
                $reorderJsFiles
            );
            $result = ['success' => true];
        } catch (CoreException $e) {
            $result = ['error' => true, 'message' => $e->getMessage()];
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => __('We cannot upload the CSS file.')];
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }
}
