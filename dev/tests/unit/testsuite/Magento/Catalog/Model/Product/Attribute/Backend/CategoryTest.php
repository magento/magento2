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
 * obtain it through the world-wide-web, please send an e-mail
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend;

class CategoryTest extends \PHPUnit_Framework_TestCase
{
    public function testAfterLoad()
    {
        $categoryIds = array(1,2,3,4,5);

        $product = $this->getMock('Magento\Object', array('getCategoryIds', 'setData'));
        $product->expects($this->once())
            ->method('getCategoryIds')
            ->will($this->returnValue($categoryIds));

        $product->expects($this->once())
            ->method('setData')
            ->with('category_ids', $categoryIds);

        $categoryAttribute = $this->getMock('Magento\Object', array('getAttributeCode'));
        $categoryAttribute->expects($this->once())
            ->method('getAttributeCode')
            ->will($this->returnValue('category_ids'));

        $logger = $this->getMock('Magento\Core\Model\Logger', array(), array(), '', false);
        $model = new \Magento\Catalog\Model\Product\Attribute\Backend\Category($logger);
        $model->setAttribute($categoryAttribute);

        $model->afterLoad($product);
    }
}
