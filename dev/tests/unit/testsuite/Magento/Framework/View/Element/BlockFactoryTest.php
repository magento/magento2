<?php
/**
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
namespace Magento\Framework\View\Element;

class BlockFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected $blockFactory;

    /**
     * @var \Magento\Framework\ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    public function setUp()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->blockFactory = $objectManagerHelper->getObject('Magento\Framework\View\Element\BlockFactory', array(
            'objectManager' => $this->objectManagerMock
        ));
    }

    public function testCreateBlock()
    {
        $className = 'Magento\Framework\View\Element\Template';
        $argumentsResult = ['arg1', 'arg2'];

        $templateMock = $this->getMockBuilder('Magento\Framework\View\Element\Template')
            ->disableOriginalConstructor()->getMock();

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with($className, $argumentsResult)
            ->will($this->returnValue($templateMock));

        $this->assertInstanceOf(
            'Magento\Framework\View\Element\BlockInterface',
            $this->blockFactory->createBlock($className, $argumentsResult)
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateBlockWithException()
    {
        $this->blockFactory->createBlock('invalid_class_name');
    }
}
