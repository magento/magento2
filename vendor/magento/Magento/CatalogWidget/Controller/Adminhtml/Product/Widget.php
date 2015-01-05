<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogWidget\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;

/**
 * Class Widget
 */
class Widget extends Action
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Widget::widget_instance');
    }
}
