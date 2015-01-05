<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Cms\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Renders CMS Home page
     *
     * @param string|null $coreRoute
     * @return void
     */
    public function execute($coreRoute = null)
    {
        $pageId = $this->_objectManager->get(
            'Magento\Framework\App\Config\ScopeConfigInterface'
        )->getValue(
            \Magento\Cms\Helper\Page::XML_PATH_HOME_PAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$this->_objectManager->get('Magento\Cms\Helper\Page')->renderPage($this, $pageId)) {
            $this->_forward('defaultIndex');
        }
    }
}
