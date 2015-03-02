<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor;

use Magento\Framework\View\Design\ThemeInterface;
use Magento\Store\Model\Store;

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
            /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
            $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
            $ids = array_keys($storeManager->getStores());
            $stores = [array_shift($ids)];
        } elseif ($stores == $emptyStores) {
            $stores = [];
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
            $response = ['message' => $successMessage, 'themeId' => $themeCustomization->getId()];
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $response = ['error' => true, 'message' => __('This theme is not assigned.')];
        }
        $this->getResponse()->representJson($coreHelper->jsonEncode($response));
    }
}
