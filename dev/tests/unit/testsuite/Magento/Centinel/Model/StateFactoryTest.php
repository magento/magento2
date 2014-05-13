<?php
/**
 * \Magento\Centinel\Model\StateFactory
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Centinel\Model;

class StateFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateState()
    {
        $objectManager = $this->getMock('Magento\Framework\ObjectManager');
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
            array(
                'VI' => 'Magento\Centinel\Model\State\Visa',
                'MC' => 'Magento\Centinel\Model\State\Mastercard',
                'JCB' => 'Magento\Centinel\Model\State\Jcb',
                'SM' => 'Magento\Centinel\Model\State\Mastercard'
            )
        );
        $this->assertInstanceOf('Magento\Centinel\Model\State\Visa', $factory->createState('VI'));
        $this->assertInstanceOf('Magento\Centinel\Model\State\Mastercard', $factory->createState('MC'));
        $this->assertInstanceOf('Magento\Centinel\Model\State\Jcb', $factory->createState('JCB'));
        $this->assertInstanceOf('Magento\Centinel\Model\State\Mastercard', $factory->createState('SM'));
        $this->assertFalse($factory->createState('LOL'));
    }

    public function testCreateStateMapIsEmpty()
    {
        $objectManager = $this->getMock('Magento\Framework\ObjectManager');
        $factory = new \Magento\Centinel\Model\StateFactory($objectManager);
        $this->assertFalse($factory->createState('VI'));
        $this->assertFalse($factory->createState('MC'));
        $this->assertFalse($factory->createState('JCB'));
        $this->assertFalse($factory->createState('SM'));
        $this->assertFalse($factory->createState('LOL'));
    }
}
