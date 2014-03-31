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
namespace Magento\GroupedProduct\Model\Product\Cart\Configuration\Plugin;

class GroupedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GroupedProduct\Model\Product\Cart\Configuration\Plugin\Grouped
     */
    protected $groupedPlugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    protected function setUp()
    {
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $this->subjectMock = $this->getMock(
            'Magento\Catalog\Model\Product\CartConfiguration',
            array(),
            array(),
            '',
            false
        );
        $this->closureMock = function () {
            return 'Expected';
        };
        $this->groupedPlugin = new \Magento\GroupedProduct\Model\Product\Cart\Configuration\Plugin\Grouped();
    }

    public function testAroundIsProductConfiguredWhenProductGrouped()
    {
        $config = array('super_group' => 'product');
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeId'
        )->will(
            $this->returnValue(\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE)
        );
        $this->assertEquals(
            true,
            $this->groupedPlugin->aroundIsProductConfigured(
                $this->subjectMock,
                $this->closureMock,
                $this->productMock,
                $config
            )
        );
    }

    public function testAroundIsProductConfiguredWhenProductIsNotGrouped()
    {
        $config = array('super_group' => 'product');
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue('product'));
        $this->assertEquals(
            'Expected',
            $this->groupedPlugin->aroundIsProductConfigured(
                $this->subjectMock,
                $this->closureMock,
                $this->productMock,
                $config
            )
        );
    }
}
