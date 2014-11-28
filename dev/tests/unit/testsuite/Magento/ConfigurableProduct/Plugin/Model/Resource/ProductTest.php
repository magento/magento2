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

namespace Magento\ConfigurableProduct\Plugin\Model\Resource;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product\Type;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    public function testBeforeSaveConfigurable()
    {
        $subject = $this->getMock('Magento\Catalog\Model\Resource\Product', [], [], '', false);
        $object = $this->getMock('Magento\Catalog\Model\Product', ['getTypeId', 'getTypeInstance'], [], '', false);
        $type = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            ['getSetAttributes'],
            [],
            '',
            false
        );
        $type->expects($this->once())->method('getSetAttributes')->with($object);

        $object->expects($this->once())->method('getTypeId')->will($this->returnValue(Configurable::TYPE_CODE));
        $object->expects($this->once())->method('getTypeInstance')->will($this->returnValue($type));

        $product = new Product();
        $product->beforeSave(
            $subject,
            $object
        );
    }

    public function testBeforeSaveSimple()
    {
        $subject = $this->getMock('Magento\Catalog\Model\Resource\Product', [], [], '', false);
        $object = $this->getMock('Magento\Catalog\Model\Product', ['getTypeId', 'getTypeInstance'], [], '', false);
        $object->expects($this->once())->method('getTypeId')->will($this->returnValue(Type::TYPE_SIMPLE));
        $object->expects($this->never())->method('getTypeInstance');

        $product = new Product();
        $product->beforeSave(
            $subject,
            $object
        );
    }
}
