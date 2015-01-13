<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Adminhtml\Index;

/**
 * Class Render
 *
 * @package Magento\Ui\Controller\Adminhtml\Index
 */
class Render extends \Magento\Ui\Controller\Adminhtml\AbstractAction
{
    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        $this->_response->appendBody(
            $this->factory->createUiComponent($this->getComponent(), $this->getName())->render()
        );
    }
}
