<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor;

class LoadThemeList extends \Magento\DesignEditor\Controller\Adminhtml\System\Design\Editor
{
    /**
     * Ajax loading available themes
     *
     * @return void
     */
    public function execute()
    {
        /** @var $coreHelper \Magento\Core\Helper\Data */
        $coreHelper = $this->_objectManager->get('Magento\Core\Helper\Data');

        $page = $this->getRequest()->getParam('page', 1);
        $pageSize = $this->getRequest()->getParam(
            'page_size',
            \Magento\Core\Model\Resource\Theme\Collection::DEFAULT_PAGE_SIZE
        );

        try {
            $this->_view->loadLayout();
            /** @var $collection \Magento\Core\Model\Resource\Theme\Collection */
            $collection = $this->_objectManager->get(
                'Magento\Core\Model\Resource\Theme\Collection'
            )->filterPhysicalThemes(
                $page,
                $pageSize
            );

            /** @var $availableThemeBlock \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList\Available */
            $availableThemeBlock = $this->_view->getLayout()->getBlock('available.theme.list');
            $availableThemeBlock->setCollection($collection)->setNextPage(++$page);
            $availableThemeBlock->setIsFirstEntrance($this->_isFirstEntrance());
            $availableThemeBlock->setHasThemeAssigned($this->_customizationConfig->hasThemeAssigned());

            $response = ['content' => $this->_view->getLayout()->getOutput()];
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $response = ['error' => __('Sorry, but we can\'t load the theme list.')];
        }
        $this->getResponse()->representJson($coreHelper->jsonEncode($response));
    }
}
