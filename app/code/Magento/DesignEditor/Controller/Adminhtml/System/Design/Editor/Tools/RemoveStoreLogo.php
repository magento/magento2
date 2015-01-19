<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor\Tools;

use Magento\Framework\Model\Exception as CoreException;

class RemoveStoreLogo extends \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor\Tools
{
    /**
     * Remove store logo
     *
     * @return void
     * @throws CoreException
     */
    public function execute()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        try {
            /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
            $theme = $this->_objectManager->create('Magento\Framework\View\Design\ThemeInterface');
            if (!$theme->load($themeId)->getId() || !$theme->isEditable()) {
                throw new CoreException(__('The file can\'t be found or edited.'));
            }

            /** @var $customizationConfig \Magento\Theme\Model\Config\Customization */
            $customizationConfig = $this->_objectManager->get('Magento\Theme\Model\Config\Customization');
            $store = $this->_objectManager->get('Magento\Store\Model\Store')->load($storeId);

            if (!$customizationConfig->isThemeAssignedToStore($theme, $store)) {
                throw new CoreException(__('This theme is not assigned to a store view #%1.', $theme->getId()));
            }

            $this->_objectManager->get(
                'Magento\Backend\Model\Config\Backend\Store'
            )->setScope(
                'stores'
            )->setScopeId(
                $store->getId()
            )->setPath(
                'design/header/logo_src'
            )->setValue(
                ''
            )->save();

            $this->_reinitSystemConfiguration();
            $response = ['error' => false, 'content' => []];
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
