<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
