<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor;

use Magento\Framework\Model\Exception as CoreException;
use Magento\Framework\View\Design\ThemeInterface;

class Duplicate extends \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor
{
    /**
     * Duplicate theme action
     *
     * @return void
     */
    public function execute()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        /** @var $themeCopy ThemeInterface */
        $themeCopy = $this->_objectManager->create('Magento\Framework\View\Design\ThemeInterface');
        /** @var $copyService \Magento\Theme\Model\CopyService */
        $copyService = $this->_objectManager->get('Magento\Theme\Model\CopyService');
        try {
            $theme = $this->_loadThemeById($themeId);
            if (!$theme->isVirtual()) {
                throw new CoreException(__('Sorry, but you can\'t edit theme "%1".', $theme->getThemeTitle()));
            }
            $themeCopy->setData($theme->getData());
            $themeCopy->setId(null)->setThemeTitle(__('Copy of [%1]', $theme->getThemeTitle()));
            $themeCopy->getThemeImage()->createPreviewImageCopy($theme);
            $themeCopy->save();
            $copyService->copy($theme, $themeCopy);
            $this->messageManager->addSuccess(__('You saved a duplicate copy of this theme in "My Customizations."'));
        } catch (CoreException $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $this->messageManager->addError(__('You cannot duplicate this theme.'));
        }
        $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
    }
}
