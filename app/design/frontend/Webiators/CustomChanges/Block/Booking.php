<?php

namespace Webiators\CustomChanges\Block;

use Magento\Framework\View\Element\Template\Context;


class Booking extends \Magento\Framework\View\Element\Template
{
    protected $_postFactory;
    public function __construct(
        Context $context,
        \Webiators\CustomChanges\Model\PostFactory $postFactory,
        array $data = []
    )
    {
        $this->_postFactory = $postFactory;
        parent::__construct($context, $data);
    }
    public function getFormAction()
    {
            // companymodule is given in routes.xml
            // controller_name is folder name inside controller folder
            // action is php file name inside above controller_name folder

        // return '/webiatormodule/index/booking';
        // here controller_name is index, action is booking
    }
}