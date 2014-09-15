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

use Magento\Store\Model\Store;
use Magento\Framework\View\Design\ThemeInterface;

class AssignThemeToStore extends \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor
{
    /**
     * Get stores
     *
     * @todo temporary method. used until we find a way to convert array to JSON on JS side
     *
     * @return Store[]
     * @throws \InvalidArgumentException
     */
    protected function _getStores()
    {
        $stores = $this->getRequest()->getParam('stores');

        $defaultStore = -1;
        $emptyStores = -2;
        if ($stores == $defaultStore) {
            /** @var \Magento\Framework\StoreManagerInterface $storeManager */
            $storeManager = $this->_objectManager->get('Magento\Framework\StoreManagerInterface');
            $ids = array_keys($storeManager->getStores());
            $stores = array(array_shift($ids));
        } elseif ($stores == $emptyStores) {
            $stores = array();
        }

        if (!is_array($stores)) {
            throw new \InvalidArgumentException('Param "stores" is not valid');
        }

        return $stores;
    }

    /**
     * Assign theme to list of store views
     *
     * @return void
     */
    public function execute()
    {
        $themeId = (int)$this->getRequest()->getParam('theme_id');
        $reportToSession = (bool)$this->getRequest()->getParam('reportToSession');

        /** @var $coreHelper \Magento\Core\Helper\Data */
        $coreHelper = $this->_objectManager->get('Magento\Core\Helper\Data');

        $hadThemeAssigned = $this->_customizationConfig->hasThemeAssigned();

        try {
            $theme = $this->_loadThemeById($themeId);

            $themeCustomization = $theme->isVirtual() ? $theme : $theme->getDomainModel(
                ThemeInterface::TYPE_PHYSICAL
            )->createVirtualTheme(
                $theme
            );

            /** @var $themeCustomization ThemeInterface */
            $this->_themeConfig->assignToStore($themeCustomization, $this->_getStores());

            $successMessage = $hadThemeAssigned ? __(
                'You assigned a new theme to your store view.'
            ) : __(
                'You assigned a theme to your live store.'
            );
            if ($reportToSession) {
                $this->messageManager->addSuccess($successMessage);
            }
            $response = array('message' => $successMessage, 'themeId' => $themeCustomization->getId());
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $response = array('error' => true, 'message' => __('This theme is not assigned.'));
        }
        $this->getResponse()->representJson($coreHelper->jsonEncode($response));
    }
}
