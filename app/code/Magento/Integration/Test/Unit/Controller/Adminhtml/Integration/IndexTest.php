<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Unit\Controller\Adminhtml\Integration;

class IndexTest extends \Magento\Integration\Test\Unit\Controller\Adminhtml\IntegrationTest
{
    public function testIndexAction()
    {
        $this->_verifyLoadAndRenderLayout();
        // renderLayout
        $this->_controller = $this->_createIntegrationController('Index');
        $result = $this->_controller->execute();
        $this->assertNull($result);
    }
}
