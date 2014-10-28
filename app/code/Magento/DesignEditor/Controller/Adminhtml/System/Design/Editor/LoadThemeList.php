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

            $response = array('content' => $this->_view->getLayout()->getOutput());
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $response = array('error' => __('Sorry, but we can\'t load the theme list.'));
        }
        $this->getResponse()->representJson($coreHelper->jsonEncode($response));
    }
}
