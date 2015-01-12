<?php
/**
 * \Magento\Centinel\Model\StateFactory
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Centinel\Model;

class StateFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateState()
    {
        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $objectManager->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            'Magento\Centinel\Model\State\Visa'
        )->will(
            $this->returnValue($this->getMock('Magento\Centinel\Model\State\Visa'))
        );
        $objectManager->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            'Magento\Centinel\Model\State\Mastercard'
        )->will(
            $this->returnValue($this->getMock('Magento\Centinel\Model\State\Mastercard'))
        );
        $objectManager->expects(
            $this->at(2)
        )->method(
            'create'
        )->with(
            'Magento\Centinel\Model\State\Jcb'
        )->will(
            $this->returnValue($this->getMock('Magento\Centinel\Model\State\Jcb'))
        );
        $objectManager->expects(
            $this->at(3)
        )->method(
            'create'
        )->with(
            'Magento\Centinel\Model\State\Mastercard'
        )->will(
            $this->returnValue($this->getMock('Magento\Centinel\Model\State\Mastercard'))
        );

        $factory = new \Magento\Centinel\Model\StateFactory(
            $objectManager,
            [
                'VI' => 'Magento\Centinel\Model\State\Visa',
                'MC' => 'Magento\Centinel\Model\State\Mastercard',
                'JCB' => 'Magento\Centinel\Model\State\Jcb',
                'SM' => 'Magento\Centinel\Model\State\Mastercard'
            ]
        );
        $this->assertInstanceOf('Magento\Centinel\Model\State\Visa', $factory->createState('VI'));
        $this->assertInstanceOf('Magento\Centinel\Model\State\Mastercard', $factory->createState('MC'));
        $this->assertInstanceOf('Magento\Centinel\Model\State\Jcb', $factory->createState('JCB'));
        $this->assertInstanceOf('Magento\Centinel\Model\State\Mastercard', $factory->createState('SM'));
        $this->assertFalse($factory->createState('LOL'));
    }

    public function testCreateStateMapIsEmpty()
    {
        $objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $factory = new \Magento\Centinel\Model\StateFactory($objectManager);
        $this->assertFalse($factory->createState('VI'));
        $this->assertFalse($factory->createState('MC'));
        $this->assertFalse($factory->createState('JCB'));
        $this->assertFalse($factory->createState('SM'));
        $this->assertFalse($factory->createState('LOL'));
    }
}
