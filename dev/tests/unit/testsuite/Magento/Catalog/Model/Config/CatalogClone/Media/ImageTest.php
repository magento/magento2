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
namespace Magento\Catalog\Model\Config\CatalogClone\Media;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\TestFramework\Helper\ObjectManager;

class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Config\CatalogClone\Media\Image
     */
    private $model;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfig;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeCollection;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attribute;

    protected function setUp()
    {
        $this->eavConfig = $this->getMockBuilder('Magento\Eav\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeCollection = $this->getMockBuilder(
            '\Magento\Catalog\Model\Resource\Product\Attribute\Collection'
        )
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributeCollectionFactory = $this->getMockBuilder(
            'Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory'
        )
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeCollectionFactory->expects($this->any())->method('create')->will(
            $this->returnValue($this->attributeCollection)
        );

        $this->attribute = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute')
            ->disableOriginalConstructor()
            ->getMock();

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            'Magento\Catalog\Model\Config\CatalogClone\Media\Image',
            [
                'eavConfig' => $this->eavConfig,
                'attributeCollectionFactory' => $this->attributeCollectionFactory
            ]
        );
    }

    public function testGetPrefixes()
    {
        $entityTypeId = 3;
        /** @var \Magento\Eav\Model\Entity\Type|\PHPUnit_Framework_MockObject_MockObject $entityType */
        $entityType = $this->getMockBuilder('Magento\Eav\Model\Entity\Type')
            ->disableOriginalConstructor()
            ->getMock();
        $entityType->expects($this->once())->method('getId')->will($this->returnValue($entityTypeId));

        /** @var AbstractFrontend|\PHPUnit_Framework_MockObject_MockObject $frontend */
        $frontend = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend')
            ->setMethods(['getLabel'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $frontend->expects($this->once())->method('getLabel')->will($this->returnValue('testLabel'));

        $this->attributeCollection->expects($this->once())->method('setEntityTypeFilter')->with(
            $this->equalTo($entityTypeId)
        );
        $this->attributeCollection->expects($this->once())->method('setFrontendInputTypeFilter')->with(
            $this->equalTo('media_image')
        );

        $this->attribute->expects($this->once())->method('getAttributeCode')->will(
            $this->returnValue('attributeCode')
        );
        $this->attribute->expects($this->once())->method('getFrontend')->will(
            $this->returnValue($frontend)
        );

        $this->attributeCollection->expects($this->any())->method('getIterator')->will(
            $this->returnValue(new \ArrayIterator([$this->attribute]))
        );

        $this->eavConfig->expects($this->any())->method('getEntityType')->with(
            $this->equalTo(Product::ENTITY)
        )->will($this->returnValue($entityType));

        $this->assertEquals([['field' => 'attributeCode_', 'label' => 'testLabel']], $this->model->getPrefixes());
    }
}
