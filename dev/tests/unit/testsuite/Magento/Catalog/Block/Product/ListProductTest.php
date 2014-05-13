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
namespace Magento\Catalog\Block\Product;

class ListProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Block\Product\ListProduct
     */
    protected $block;

    /**
     * @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layerMock;

    /**
     * @var \Magento\Core\Helper\PostData|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $postDataHelperMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Checkout\Helper\Cart|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartHelperMock;

    /**
     * @var \Magento\Catalog\Model\Product\Type\Simple|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeInstanceMock;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->registryMock = $this->getMock('Magento\Framework\Registry', array(), array(), '', false);
        $this->layerMock = $this->getMock('Magento\Catalog\Model\Layer', array(), array(), '', false);
        $this->postDataHelperMock = $this->getMock(
            'Magento\Core\Helper\PostData',
            array(),
            array(),
            '',
            false
        );
        $this->typeInstanceMock = $this->getMock(
            'Magento\Catalog\Model\Product\Type\Simple',
            array(),
            array(),
            '',
            false,
            false
        );
        $this->productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            array(),
            array(),
            '',
            false
        );
        $this->cartHelperMock = $this->getMock(
            'Magento\Checkout\Helper\Cart',
            array(),
            array(),
            '',
            false
        );
        $this->block = $objectManager->getObject(
            'Magento\Catalog\Block\Product\ListProduct',
            array(
                'registry' => $this->registryMock,
                'catalogLayer' => $this->layerMock,
                'cartHelper' => $this->cartHelperMock,
                'postDataHelper' => $this->postDataHelperMock
            )
        );
    }

    protected function tearDown()
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $productTag = 'catalog_product_1';
        $categoryTag = 'catalog_category_product_1';

        $this->productMock->expects($this->once())
            ->method('getIdentities')
            ->will($this->returnValue(array($productTag)));

        $itemsCollection = new \ReflectionProperty('Magento\Catalog\Block\Product\ListProduct', '_productCollection');
        $itemsCollection->setAccessible(true);
        $itemsCollection->setValue($this->block, array($this->productMock));

        $currentCategory = $this->getMock('Magento\Catalog\Model\Category', array(), array(), '', false);
        $currentCategory->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('1'));

        $this->layerMock->expects($this->once())
            ->method('getCurrentCategory')
            ->will($this->returnValue($currentCategory));

        $this->assertEquals(
            array($productTag, $categoryTag ),
            $this->block->getIdentities()
        );
    }

    public function testGetAddToCartPostParams()
    {
        $url = 'http://localhost.com/dev/';
        $id = 1;
        $uenc = strtr(base64_encode($url), '+/=', '-_,');
        $data = array('product' => $id, \Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED => $uenc);
        $expectedPostData = json_encode(
            array(
                'action' => $url,
                'data' => array('product' => $id, 'uenc' => $uenc)
            )
        );

        $this->typeInstanceMock->expects($this->once())
            ->method('hasRequiredOptions')
            ->with($this->equalTo($this->productMock))
            ->will($this->returnValue(false));
        $this->cartHelperMock->expects($this->any())
            ->method('getAddUrl')
            ->with($this->equalTo($this->productMock), $this->equalTo(array()))
            ->will($this->returnValue($url));
        $this->productMock->expects($this->once())
            ->method('getEntityId')
            ->will($this->returnValue($id));
        $this->productMock->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($this->typeInstanceMock));
        $this->postDataHelperMock->expects($this->once())
            ->method('getEncodedUrl')
            ->with($this->equalTo($url))
            ->will($this->returnValue($uenc));
        $this->postDataHelperMock->expects($this->once())
            ->method('getPostData')
            ->with($this->equalTo($url), $this->equalTo($data))
            ->will($this->returnValue($expectedPostData));
        $result = $this->block->getAddToCartPostParams($this->productMock);
        $this->assertEquals($expectedPostData, $result);
    }
}
