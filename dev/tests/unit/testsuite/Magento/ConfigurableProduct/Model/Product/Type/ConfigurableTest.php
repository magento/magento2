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

namespace Magento\ConfigurableProduct\Model\Product\Type;

/**
 * Class \Magento\ConfigurableProduct\Model\Product\Type\ConfigurableTest
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ConfigurableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_model;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectHelper;

    protected function setUp()
    {
        $this->_objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $eventManager = $this->getMock('Magento\Event\ManagerInterface', array(), array(), '', false);
        $coreDataMock = $this->getMock('Magento\Core\Helper\Data', array(), array(), '', false);
        $fileStorageDbMock = $this->getMock('Magento\Core\Helper\File\Storage\Database', array(), array(), '', false);
        $filesystem = $this->getMockBuilder('Magento\App\Filesystem')->disableOriginalConstructor()->getMock();
        $coreRegistry = $this->getMock('Magento\Registry', array(), array(), '', false);
        $logger = $this->getMock('Magento\Logger', array(), array(), '', false);
        $productFactoryMock = $this->getMock('Magento\Catalog\Model\ProductFactory', array(), array(), '', false);
        $confFactoryMock = $this->getMock('Magento\ConfigurableProduct\Model\Resource\Product\Type\ConfigurableFactory',
            array(), array(), '', false);
        $entityFactoryMock = $this->getMock('Magento\Eav\Model\EntityFactory', array(), array(), '', false);
        $setFactoryMock = $this->getMock('Magento\Eav\Model\Entity\Attribute\SetFactory', array(), array(), '', false);
        $attributeFactoryMock = $this->getMock('Magento\Catalog\Model\Resource\Eav\AttributeFactory', array(),
            array(), '', false);
        $confAttrFactoryMock = $this->getMock(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable\AttributeFactory',
            array(), array(), '', false);
        $productColFactory = $this->getMock(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Product\CollectionFactory',
            array(), array(), '', false
        );
        $attrColFactory = $this->getMock(
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\CollectionFactory',
            array(), array(), '', false
        );
        $this->_model = $this->_objectHelper->getObject(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable',
            array(
                'productFactory' => $productFactoryMock,
                'typeConfigurableFactory' => $confFactoryMock,
                'entityFactory' => $entityFactoryMock,
                'attributeSetFactory' => $setFactoryMock,
                'eavAttributeFactory' => $attributeFactoryMock,
                'configurableAttributeFactory' => $confAttrFactoryMock,
                'productCollectionFactory' => $productColFactory,
                'attributeCollectionFactory' => $attrColFactory,
                'eventManager' => $eventManager,
                'coreData' => $coreDataMock,
                'fileStorageDb' => $fileStorageDbMock,
                'filesystem' => $filesystem,
                'coreRegistry' => $coreRegistry,
                'logger' => $logger
            )
        );
    }

    public function testHasWeightTrue()
    {
        $this->assertTrue($this->_model->hasWeight(), 'This product has not weight, but it should');
    }
}
