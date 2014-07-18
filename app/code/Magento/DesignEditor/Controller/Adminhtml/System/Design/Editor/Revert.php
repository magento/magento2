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
            $response = array('message' => $message);
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $response = array('error' => true, 'message' => __('Unknown error'));
        }
        /** @var $coreHelper \Magento\Core\Helper\Data */
        $coreHelper = $this->_objectManager->get('Magento\Core\Helper\Data');
        $this->getResponse()->representJson($coreHelper->jsonEncode($response));
    }
}
