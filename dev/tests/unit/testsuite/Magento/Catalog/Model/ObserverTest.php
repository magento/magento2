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
 * @package     Magento_Catalog
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model;

/**
 * Class \Magento\Catalog\Model\ObserverTest
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectHelper;

    /**
     * @var \Magento\Event\Observer
     */
    protected $_observer;

    /**
     * @var \Magento\Catalog\Model\Observer
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    protected function setUp()
    {
        $this->_objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_catalogCategory = $this->getMock('Magento\Catalog\Helper\Category', array(), array(), '', false);
        $this->_catalogData = $this->getMock('Magento\Catalog\Helper\Data', array(), array(), '', false);
        $urlFactoryMock = $this->getMock('Magento\Catalog\Model\UrlFactory', array(), array(), '', false);
        $catFlatFactoryMock = $this->getMock('Magento\Catalog\Model\Resource\Category\FlatFactory', array(),
            array(), '', false);
        $productFactoryMock = $this->getMock('Magento\Catalog\Model\Resource\ProductFactory', array(),
            array(), '', false);
        $this->_catalogCategoryFlat = $this->getMock(
            'Magento\Catalog\Helper\Category\Flat', array(), array(), '', false
        );
        $coreConfig = $this->getMock('Magento\Core\Model\Config', array(), array(), '', false);
        $this->_model = $this->_objectHelper->getObject('Magento\Catalog\Model\Observer', array(
            'catalogCategory' => $this->_catalogCategory,
            'catalogData' => $this->_catalogData,
            'catalogCategoryFlat' => $this->_catalogCategoryFlat,
            'coreConfig' => $coreConfig,
            'urlFactory' => $urlFactoryMock,
            'flatResourceFactory' => $catFlatFactoryMock,
            'productResourceFactory' => $productFactoryMock,
        ));
        $this->_requestMock = $this->getMock('Magento\App\RequestInterface', array(), array(), '', false);
    }

    public function testTransitionProductTypeSimple()
    {
        $product = new \Magento\Object(array('type_id' => 'simple'));
        $this->_observer = new \Magento\Event\Observer(array('product' => $product, 'request' => $this->_requestMock));
        $this->_model->transitionProductType($this->_observer);
        $this->assertEquals('simple', $product->getTypeId());
    }

    public function testTransitionProductTypeVirtual()
    {
        $product = new \Magento\Object(array('type_id' => 'virtual', 'is_virtual' => ''));
        $this->_observer = new \Magento\Event\Observer(array('product' => $product, 'request' => $this->_requestMock));
        $this->_model->transitionProductType($this->_observer);
        $this->assertEquals('virtual', $product->getTypeId());
    }

    public function testTransitionProductTypeSimpleToVirtual()
    {
        $product = new \Magento\Object(array('type_id' => 'simple', 'is_virtual' => ''));
        $this->_observer = new \Magento\Event\Observer(array('product' => $product, 'request' => $this->_requestMock));
        $this->_model->transitionProductType($this->_observer);
        $this->assertEquals('virtual', $product->getTypeId());
    }

    public function testTransitionProductTypeVirtualToSimple()
    {
        $product = new \Magento\Object(array('type_id' => 'virtual'));
        $this->_observer = new \Magento\Event\Observer(array('product' => $product, 'request' => $this->_requestMock));
        $this->_model->transitionProductType($this->_observer);
        $this->assertEquals('simple', $product->getTypeId());
    }

    public function testTransitionProductTypeConfigurableToSimple()
    {
        $product = new \Magento\Object(array('type_id' => 'configurable'));
        $this->_observer = new \Magento\Event\Observer(array('product' => $product, 'request' => $this->_requestMock));
        $this->_model->transitionProductType($this->_observer);
        $this->assertEquals('simple', $product->getTypeId());
    }

    public function testTransitionProductTypeConfigurableToVirtual()
    {
        $product = new \Magento\Object(array('type_id' => 'configurable', 'is_virtual' => '1'));
        $this->_observer = new \Magento\Event\Observer(array('product' => $product, 'request' => $this->_requestMock));
        $this->_model->transitionProductType($this->_observer);
        $this->assertEquals('virtual', $product->getTypeId());
    }
}
