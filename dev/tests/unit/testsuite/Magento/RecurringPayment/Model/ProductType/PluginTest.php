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
namespace Magento\RecurringPayment\Model\ProductType;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Plugin
     */
    protected $object;

    /**
     * @var \Magento\Catalog\Model\Product\Type\AbstractType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subject;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    protected function setUp()
    {
        $this->subject = $this->getMock(
            'Magento\Catalog\Model\Product\Type\AbstractType',
            array(),
            array(),
            '',
            false
        );
        $this->product = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('getIsRecurring', '__wakeup', '__sleep'),
            array(),
            '',
            false
        );
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->object = $objectManager->getObject('Magento\RecurringPayment\Model\ProductType\Plugin');
    }

    public function testAroundHasOptionsForProductWithRecurringPayment()
    {
        $this->product->expects($this->once())->method('getIsRecurring')->will($this->returnValue(true));
        $closure = function () {
            throw new \Exception();
        };
        $this->assertEquals(true, $this->object->aroundHasOptions($this->subject, $closure, $this->product));
    }

    public function testAroundHasOptionsForProductWithoutRecurringPayment()
    {
        $this->product->expects($this->once())->method('getIsRecurring')->will($this->returnValue(false));
        $closure = function ($product) {
            return $product;
        };
        $this->assertSame($this->product, $this->object->aroundHasOptions($this->subject, $closure, $this->product));
    }
}
