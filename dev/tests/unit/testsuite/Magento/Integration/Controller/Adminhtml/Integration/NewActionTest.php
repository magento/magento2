<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
