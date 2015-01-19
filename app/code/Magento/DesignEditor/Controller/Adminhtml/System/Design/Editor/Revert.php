<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor;

use Magento\Framework\Model\Exception as CoreException;
use Magento\Framework\View\Design\ThemeInterface;

class Revert extends \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor
{
    /**
     * Revert 'staging' theme to the state of 'physical' or 'virtual'
     *
     * @return void
     * @throws CoreException
     */
    public function execute()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        $revertTo = $this->getRequest()->getParam('revert_to');

        $virtualTheme = $this->_loadThemeById($themeId);
        if (!$virtualTheme->isVirtual()) {
            throw new CoreException(__('Theme "%1" is not editable.', $virtualTheme->getId()));
        }

        try {
            /** @var $copyService \Magento\Theme\Model\CopyService */
            $copyService = $this->_objectManager->get('Magento\Theme\Model\CopyService');
            $stagingTheme = $virtualTheme->getDomainModel(ThemeInterface::TYPE_VIRTUAL)->getStagingTheme();
            switch ($revertTo) {
                case 'last_saved':
                    $copyService->copy($virtualTheme, $stagingTheme);
                    $message = __('Theme "%1" reverted to last saved state', $virtualTheme->getThemeTitle());
                    break;

                case 'physical':
                    $physicalTheme = $virtualTheme->getDomainModel(ThemeInterface::TYPE_VIRTUAL)->getPhysicalTheme();
                    $copyService->copy($physicalTheme, $stagingTheme);
                    $message = __('Theme "%1" reverted to last default state', $virtualTheme->getThemeTitle());
                    break;

                default:
                    throw new \Magento\Framework\Exception('Invalid revert mode "%s"', $revertTo);
            }
            $response = ['message' => $message];
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $response = ['error' => true, 'message' => __('Unknown error')];
        }
        /** @var $coreHelper \Magento\Core\Helper\Data */
        $coreHelper = $this->_objectManager->get('Magento\Core\Helper\Data');
        $this->getResponse()->representJson($coreHelper->jsonEncode($response));
    }
}
