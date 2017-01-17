<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Controller\Test\Unit\Controller\Index;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $objectManager = new ObjectManager($this);
        /**
         * @var \Magento\Framework\Controller\Index\Index
         */
        $controller = $objectManager->getObject(\Magento\Framework\Controller\Index\Index::class);

        // The execute method is empty and returns void, just calling to verify
        // the method exists and does not throw an exception
        $controller->execute();
    }
}
