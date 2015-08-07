<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Observer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class UnsetAllTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Checkout\Observer\UnsetAll */
    protected $object;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    /** @var PHPUnit_Framework_MockObject_MockObject */
    protected $checkoutSession;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->checkoutSession = $this->getMock('Magento\Checkout\Model\Session', [], [], '', false);
        $this->object = $this->objectManager->getObject('Magento\Checkout\Observer\UnsetAll', [
            'checkoutSession' => $this->checkoutSession,
        ]);
    }

    public function testUnsetAll()
    {
        $this->checkoutSession->expects($this->once())->method('clearQuote')->will($this->returnSelf());
        $this->checkoutSession->expects($this->once())->method('clearStorage')->will($this->returnSelf());

        $this->object->invoke();
    }
}
