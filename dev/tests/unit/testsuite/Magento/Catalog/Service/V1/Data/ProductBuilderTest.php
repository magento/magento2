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
namespace Magento\Catalog\Service\V1\Data;

class ProductBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Service\V1\Data\ProductBuilder|\PHPUnit_Framework_TestCase */
    protected $_productBuilder;

    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $_objectManager;

    /** @var \Magento\Catalog\Service\V1\MetadataService */
    private $_productMetadataService;

    /** @var \Magento\Framework\Service\Data\AttributeValueBuilder */
    private $_valueBuilder;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        
        $this->_productMetadataService = $this->getMockBuilder(
            'Magento\Catalog\Service\V1\Product\MetadataService'
        )->setMethods(
            array('getCustomAttributesMetadata')
        )->disableOriginalConstructor()->getMock();
        $this->_productMetadataService
            ->expects($this->any())
            ->method('getCustomAttributesMetadata')
            ->will($this->returnValue(
                array(
                    new \Magento\Framework\Object(array('attribute_code' => 'attribute_code_1')),
                    new \Magento\Framework\Object(array('attribute_code' => 'attribute_code_2'))
                )
            )
        );
        $this->_valueBuilder = $this->_objectManager->getObject(
            'Magento\Framework\Service\Data\AttributeValueBuilder'
        );
        $this->_productBuilder = $this->_objectManager->getObject(
            'Magento\Catalog\Service\V1\Data\ProductBuilder',
            [
                'valueBuilder' => $this->_valueBuilder,
                'metadataService' => $this->_productMetadataService
            ]
        );
    }

    /**
     * @param $method
     * @param $value
     * @param $getMethod
     *
     * @dataProvider setValueDataProvider
     */
    public function testSetValue($method, $value, $getMethod)
    {
        $productData = $this->_productBuilder->$method($value)->create();
        $this->assertEquals($value, $productData->$getMethod());
    }

    /**
     * @return array
     */
    public function setValueDataProvider()
    {
        return [
            ['setSku', 'product_sku', 'getSku'],
            ['setName', 'buhanka hleba', 'getName'],
            ['setStoreId', 0, 'getStoreId'],
            ['setPrice', 100.00, 'getPrice'],
            ['setVisibility', 1, 'getVisibility'],
            ['setTypeId', 2, 'getTypeId'],
            ['setStatus', 2, 'getStatus'],
            ['setWeight', 72.5, 'getWeight']
        ];
    }

    /**
     * @return array
     */
    public function readonlyFieldProvider()
    {
        return [
            ['setCreatedAt', '2014-05-23', 'getCreatedAt'],
            ['setUpdatedAt', '2014-05-25', 'getUpdatedAt'],
        ];
    }
    /**
     * @dataProvider readonlyFieldProvider
     * @expectedException \Magento\Framework\Exception\InputException
     */
    public function testReadonlyFields($method)
    {
        $this->_productBuilder->$method('');
    }
}
