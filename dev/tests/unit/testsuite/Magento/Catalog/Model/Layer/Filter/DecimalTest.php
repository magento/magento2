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
namespace Magento\Catalog\Model\Layer\Filter;

class DecimalTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorRequestVarIsOverwrittenCorrectlyInParent()
    {
        $attributeModel = $this->getMock(
            'Magento\Catalog\Model\Resource\Eav\Attribute',
            array('getAttributeCode', '__wakeup'),
            array(),
            '',
            false
        );
        $attributeModel->expects($this->once())->method('getAttributeCode')->will($this->returnValue('price1'));

        $filterDecimalFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Layer\Filter\DecimalFactory',
            array('create')
        );
        $filterDecimalFactory->expects($this->once())->method('create')->will(
            $this->returnValue(
                $this->getMock('Magento\Catalog\Model\Resource\Layer\Filter\Decimal', array(), array(), '', false)
            )
        );
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $instance = $objectManager->getObject(
            'Magento\Catalog\Model\Layer\Filter\Decimal',
            array(
                'filterDecimalFactory' => $filterDecimalFactory,
                'data' => array('attribute_model' => $attributeModel)
            )
        );
        $this->assertSame('price1', $instance->getRequestVar());
    }
}
