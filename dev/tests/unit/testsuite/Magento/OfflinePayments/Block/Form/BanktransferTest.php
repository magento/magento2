<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflinePayments\Block\Form;

class BanktransferTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflinePayments\Block\Form\Banktransfer
     */
    protected $_object;

    protected function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_object = $objectManagerHelper->getObject('Magento\OfflinePayments\Block\Form\Banktransfer');
    }

    public function testGetInstructions()
    {
        $method = $this->getMock(
            'Magento\Payment\Model\MethodInterface',
            ['getInstructions', 'getCode', 'getFormBlockType', 'getTitle'],
            [],
            '',
            false
        );
        $method->expects($this->once())
            ->method('getInstructions')
            ->willReturn('instructions');
        $this->_object->setData('method', $method);

        $this->assertEquals('instructions', $this->_object->getInstructions());
    }
}
