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

namespace Magento\Bundle\Model\Plugin;

use Magento\Catalog\Model\Product;
use Magento\TestFramework\Helper\ObjectManager;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ProductTest extends \PHPUnit_Framework_TestCase
{

    /** @var  \Magento\Bundle\Model\Plugin\Product */
    private $plugin;

    /** @var  MockObject|\Magento\Bundle\Model\Product\Type */
    private $type;

    /** @var  MockObject|\Magento\Catalog\Model\Product */
    private $product;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->product = $this->getMockBuilder('\Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $this->type = $this->getMockBuilder('\Magento\Bundle\Model\Product\Type')
            ->disableOriginalConstructor()
            ->setMethods(['getParentIdsByChild'])
            ->getMock();

        $this->plugin = $objectManager->getObject(
            '\Magento\Bundle\Model\Plugin\Product',
            [
                'type' => $this->type,
            ]
        );
    }

    public function testAfterGetIdentities()
    {
        $baseIdentities = [
            'SomeCacheId',
            'AnotherCacheId',
        ];
        $id = 12345;
        $parentIds = [1, 2, 5, 100500];
        $expectedIdentities = [
            'SomeCacheId',
            'AnotherCacheId',
            Product::CACHE_TAG . '_' . 1,
            Product::CACHE_TAG . '_' . 2,
            Product::CACHE_TAG . '_' . 5,
            Product::CACHE_TAG . '_' . 100500,
        ];
        $this->product->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($id));
        $this->type->expects($this->once())
            ->method('getParentIdsByChild')
            ->with($id)
            ->will($this->returnValue($parentIds));
        $identities = $this->plugin->afterGetIdentities($this->product, $baseIdentities);
        $this->assertEquals($expectedIdentities, $identities);
    }
}
