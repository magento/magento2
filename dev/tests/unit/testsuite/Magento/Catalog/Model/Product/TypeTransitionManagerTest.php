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
namespace Magento\Catalog\Model\Product;

class TypeTransitionManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    protected function setUp()
    {
        $this->model = new TypeTransitionManager(
            array(
                'simple' => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                'virtual' => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL
            )
        );
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            array('hasIsVirtual', 'getTypeId', 'setTypeId', 'setTypeInstance', '__wakeup'),
            array(),
            '',
            false
        );
    }

    /**
     * @param bool $isVirtual
     * @param string $currentTypeId
     * @param string $expectedTypeId
     * @dataProvider processProductDataProvider
     */
    public function testProcessProduct($isVirtual, $currentTypeId, $expectedTypeId)
    {
        $this->productMock->expects($this->any())->method('hasIsVirtual')->will($this->returnValue($isVirtual));
        $this->productMock->expects($this->once())->method('getTypeId')->will($this->returnValue($currentTypeId));
        $this->productMock->expects($this->once())->method('setTypeInstance')->with(null);
        $this->productMock->expects($this->once())->method('setTypeId')->with($expectedTypeId);
        $this->model->processProduct($this->productMock);
    }

    /**
     * @return array
     */
    public function processProductDataProvider()
    {
        return array(
            array(
                false,
                \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            ),
            array(
                false,
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE
            ),
            array(
                true,
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL
            ),
            array(
                true,
                \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
                \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL
            )
        );
    }
}
