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
namespace Magento\Sales\Model;

/**
 * Class IncrementTest
 */
class IncrementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Increment
     */
    protected $model;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Model\Entity\Type|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $type;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->eavConfig = $this->getMock('Magento\Eav\Model\Config', ['getEntityType'], [], '', false);
        $this->model = $objectManager->getObject('Magento\Sales\Model\Increment', ['eavConfig' => $this->eavConfig]);
        $this->type = $this->getMock('Magento\Eav\Model\Entity\Type', ['fetchNewIncrementId'], [], '', false);
    }

    public function testGetCurrentValue()
    {
        $this->type->expects($this->once())
            ->method('fetchNewIncrementId')
            ->with(1)
            ->willReturn(2);
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with('order')
            ->willReturn($this->type);
        $this->model->getNextValue(1);
        $this->assertEquals(2, $this->model->getCurrentValue());
    }

    public function testNextValue()
    {
        $this->type->expects($this->once())
            ->method('fetchNewIncrementId')
            ->with(1)
            ->willReturn(2);
        $this->eavConfig->expects($this->once())
            ->method('getEntityType')
            ->with('order')
            ->willReturn($this->type);
        $this->assertEquals(2, $this->model->getNextValue(1));
    }
}
