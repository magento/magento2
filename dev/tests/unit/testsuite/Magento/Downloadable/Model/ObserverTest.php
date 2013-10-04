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
 * @category    Magento
 * @package     Magento_Downloadable
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Downloadable\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Event\Observer
     */
    protected $_observer;

    /**
     * @var \Magento\Downloadable\Model\Observer
     */
    protected $_model;

    /**
     * @var \Magento\Core\Helper\Data
     */
    protected $_helperJsonEncode;

    protected function setUp()
    {
        $this->_helperJsonEncode = $this->getMockBuilder('Magento\Core\Helper\Data')
            ->setMethods(array('jsonEncode'))
            ->disableOriginalConstructor()
            ->getMock();
        $itemsFactory = $this->getMock('Magento\Downloadable\Model\Resource\Link\Purchased\Item\CollectionFactory',
            array(), array(), '', false
        );
        $this->_model = new \Magento\Downloadable\Model\Observer(
            $this->_helperJsonEncode,
            $this->getMock('Magento\Core\Model\Store\Config', array(), array(), '', false),
            $this->getMock('Magento\Downloadable\Model\Link\PurchasedFactory', array(), array(), '', false),
            $this->getMock('Magento\Catalog\Model\ProductFactory', array(), array(), '', false),
            $this->getMock('Magento\Downloadable\Model\Link\Purchased\ItemFactory', array(), array(), '', false),
            $this->getMock('Magento\Checkout\Model\Session', array(), array(), '', false),
            $itemsFactory
        );
    }

    protected function tearDown()
    {
        $this->_helperJsonEncode = null;
        $this->_model = null;
        $this->_observer = null;
    }

    public function testDuplicateProductNotDownloadable()
    {
        $currentProduct = $this->getMock('Magento\Catalog\Model\Product', array('getTypeId'), array(), '', false);

        $currentProduct->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE));
        $currentProduct->expects($this->never())
            ->method('getTypeInstance');

        $this->_setObserverExpectedMethods($currentProduct, new \Magento\Object());

        $this->_model->duplicateProduct($this->_observer);
    }

    public function testDuplicateProductEmptyLinks()
    {
        $currentProduct = $this->getMock('Magento\Catalog\Model\Product',
            array('getTypeId', 'getTypeInstance'), array(), '', false);
        $currentProduct->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE));
        $newProduct = $this->getMock('Magento\Catalog\Model\Product',
            array('getTypeId', 'getTypeInstance'), array(), '', false);

        $typeInstance = $this->getMock('Magento\Downloadable\Model\Product\Type',
            array('getLinks', 'getSamples'), array(), '', false);
        $typeInstance->expects($this->once())
            ->method('getLinks')
            ->will($this->returnValue(array()));
        $typeInstance->expects($this->once())
            ->method('getSamples')
            ->will($this->returnValue(new \Magento\Object()));

        $currentProduct->expects($this->once())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstance));

        $this->_setObserverExpectedMethods($currentProduct, $newProduct);

        $this->assertNull($newProduct->getDownloadableData());
        $this->_model->duplicateProduct($this->_observer);
        $this->assertEmpty($newProduct->getDownloadableData());
    }

    public function testDuplicateProductTypeFile()
    {
        $currentProduct = $this->getMock('Magento\Catalog\Model\Product',
            array('getTypeId', 'getTypeInstance'), array(), '', false);
        $currentProduct->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue(\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE));

        $newProduct = $this->getMock('Magento\Catalog\Model\Product',
            array('getTypeId', 'getTypeInstance'), array(), '', false);

        $links = $this->_getLinks();

        $samples = $this->_getSamples();

        $getLinks = new \Magento\Object($links);

        $getSamples = new \Magento\Object($samples);

        $typeInstance = $this->getMock('Magento\Downloadable\Model\Product\Type',
            array('getLinks', 'getSamples'), array(), '', false);
        $typeInstance->expects($this->atLeastOnce())
            ->method('getLinks')
            ->will($this->returnValue(array($getLinks)));
        $typeInstance->expects($this->atLeastOnce())
            ->method('getSamples')
            ->will($this->returnValue(array($getSamples)));

        $currentProduct->expects($this->atLeastOnce())
            ->method('getTypeInstance')
            ->will($this->returnValue($typeInstance));

        $this->_setObserverExpectedMethods($currentProduct, $newProduct);

        $callbackJsonEncode = function ($arg) {
            return json_encode($arg);
        };
        $this->_helperJsonEncode->expects($this->atLeastOnce())
            ->method('jsonEncode')
            ->will($this->returnCallback($callbackJsonEncode));

        $this->assertNull($newProduct->getDownloadableData($newProduct));
        $this->_model->duplicateProduct($this->_observer);

        $newDownloadableData = $newProduct->getDownloadableData();
        $fileData = json_decode($newDownloadableData['link'][0]['file'], true);

        $this->assertEquals($links['price'], $newDownloadableData['link'][0]['price']);
        $this->assertEquals($links['link_file'][0], $fileData[0]['file'][0]);
        $this->assertEquals($samples['title'], $newDownloadableData['sample'][0]['title']);
        $this->assertEquals(false, $newDownloadableData['link'][0]['is_delete']);
        $this->assertEquals($links['number_of_downloads'], $newDownloadableData['link'][0]['number_of_downloads']);
    }

    /**
     * Get downloadable data without is_delete flag
     *
     * @return array
     */
    protected function _getDownloadableData()
    {
        return array(
            'sample' => array(array('id' => 1, 'is_delete' => '')),
            'link' => array(array('id' => 2, 'is_delete' => ''))
        );
    }

    /**
     * Get downloadable data with set is_delete flag
     *
     * @return array
     */
    protected function _getDownloadableDataForDelete()
    {
        return array(
            'sample' => array(array('id' => 1, 'is_delete' => '1')),
            'link' => array(array('id' => 2, 'is_delete' => '1'))
        );
    }

    /**
     * Set products to observer
     *
     * @param array $currentProduct
     * @param array $newProduct
     */
    protected function _setObserverExpectedMethods($currentProduct, $newProduct)
    {
        $this->_observer = $this->getMock('Magento\Event\Observer',
            array('getCurrentProduct', 'getNewProduct'), array(), '', false);
        $this->_observer->expects($this->once())
            ->method('getCurrentProduct')
            ->will($this->returnValue($currentProduct));
        $this->_observer->expects($this->once())
            ->method('getNewProduct')
            ->will($this->returnValue($newProduct));
    }

    /**
     * Get Downloadable Link Data
     *
     * @return array
     */
    protected function _getLinks()
    {
        return array(
            'link_id' => '36',
            'product_id' => '141',
            'sort_order' => '0',
            'number_of_downloads' => '0',
            'is_shareable' => '2',
            'link_url' => null,
            'link_file' => array(array(
                'file'        => '/l/i/lighthouse_3.jpg',
                'name'        => 'lighthouse_3.jpg',
                'size'        => 56665,
                'status'      => 'new',
                )),
            'link_type' => 'file',
            'sample_url' => null,
            'sample_file' => array(array(
                'file'        => '/a/b/lighthouse_3.jpg',
                'name'        => 'lighthouse_3.jpg',
                'size'        => 56665,
                'status'      => 'new',
                )),
            'sample_type' => 'file',
            'title' =>'Link Title',
            'price' =>'15.00',
        );
    }

    /**
     * Get Downloadable Sample Data
     *
     * @return array
     */
    protected function _getSamples()
    {
        return array(
            'sample_id' => '42',
            'sample_url' => null,
            'sample_file' => array(array(
                'file'        => '/b/r/lighthouse_3.jpg',
                'name'        => 'lighthouse_3.jpg',
                'size'        => 56665,
                'status'      => 'new',
                )),
            'sample_type' => 'file',
            'sort_order' => '0',
            'title' => 'Sample Title',
        );
    }
}
