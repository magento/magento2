<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->messageManager->addError(__('You cannot duplicate this theme.'));
        }
        $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
    }
}
