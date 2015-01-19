<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Integration\Controller\Adminhtml\Integration;

class NewActionTest extends \Magento\Integration\Controller\Adminhtml\IntegrationTest
{
    public function testNewAction()
    {
        $this->_verifyLoadAndRenderLayout();
        // verify the request is forwarded to 'edit' action
        $this->_requestMock->expects(
            $this->any()
        )->method(
                'setActionName'
            )->with(
                'edit'
            )->will(
                $this->returnValue($this->_requestMock)
            );
        $integrationContr = $this->_createIntegrationController('NewAction');
        $integrationContr->execute();
    }
}
