<?php

namespace Alexx\Blog\Controller\Adminhtml\Index;

/**
 * Class NewAction Admin Controller
 */
class NewAction extends Edit
{
    /**
     * Create new product page
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
