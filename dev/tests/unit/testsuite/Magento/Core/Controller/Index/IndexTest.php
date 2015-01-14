<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Controller\Index;

use Magento\TestFramework\Helper\ObjectManager;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $objectManager = new ObjectManager($this);
        /**
         * @var \Magento\Core\Controller\Index
         */
        $controller = $objectManager->getObject('Magento\Core\Controller\Index\Index');

        // The execute method is empty and returns void, just calling to verify
        // the method exists and does not throw an exception
        $controller->execute();
    }
}
