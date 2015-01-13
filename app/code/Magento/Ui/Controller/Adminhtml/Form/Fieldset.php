<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Adminhtml\Form;

/**
 * Class Fieldset
 *
 * @package Magento\Ui\Controller\Adminhtml\Form
 */
class Fieldset extends \Magento\Ui\Controller\Adminhtml\AbstractAction
{
    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        //        $component = $this->getComponent();
//        $name = $this->getName();
//        if ($component && $name) {
//            $fieldset = $this->factory->createUiComponent($this->getComponent(), $this->getName())->getContainer($this->_request->getParam('container'));
//            $fieldset->setNotLoadByAjax();
//            $this->_response->appendBody(
//                $fieldset->render()
//            );
//        } else {
//            $this->_redirect('admin');
//        }
//        $tabIndex = $this->getRequest()->getParam('container');
//        $this->getResponse()->appendBody(
//            json_encode(
//                ['layout' => ['customer_form_tabs' => [$tabIndex => ['label' => 'loaded', 'content' => 'content is loaded']]]]
//            )
//        );
    }
}
