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
namespace Magento\Catalog\Service\V1;

use Magento\Catalog\Service\V1\ProductMetadataServiceInterface;

class ProductAttributeServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for retrieving attribute options
     */
    public function testOptions()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $attributeCode = 'attr_code';
        $metadataServiceMock = $this->getMock(
            'Magento\Catalog\Service\V1\ProductMetadataService',
            array('getAttributeMetadata'),
            array(),
            '',
            false
        );

        $mock = $this->getMock(
            'Magento\Catalog\Service\V1\Data\Eav\AttributeMetadata',
            array('getOptions'),
            array(),
            '',
            false
        );

        $options = array();
        $mock->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue($options));

        $metadataServiceMock->expects($this->once())
            ->method('getAttributeMetadata')
            ->with(
                ProductMetadataServiceInterface::ENTITY_TYPE_PRODUCT,
                $attributeCode
            )
            ->will($this->returnValue($mock));

        /** @var \Magento\Catalog\Service\V1\ProductAttributeServiceInterface $service */
        $service = $objectManager->getObject(
            'Magento\Catalog\Service\V1\ProductAttributeService',
            array(
                'metadataService' => $metadataServiceMock
            )
        );
        $this->assertEquals($options, $service->options($attributeCode));
    }

    /**
     * Build label
     *
     * @param $labelText
     * @param $storeId
     * @return \Magento\Catalog\Service\V1\Data\Eav\Option\Label
     */
    private function buildLabel($labelText, $storeId)
    {
        $label = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Eav\Option\Label')
            ->disableOriginalConstructor()->getMock();

        $label->expects($this->any())
            ->method('getLabel')->will($this->returnValue($labelText));

        $label->expects($this->any())
            ->method('getStoreID')->will($this->returnValue($storeId));

        return $label;
    }

    /**
     * Test for retrieving attribute options
     */
    public function testAddOption()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $attributeCode = 'attr_code';

        $label = $this->buildLabel('st 42', 42);

        $attribute = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->disableOriginalConstructor()->getMock();
        $attribute->expects($this->any())
            ->method('usesSource')
            ->will($this->returnValue(true));

        $attribute->expects($this->at(1))
            ->method('__call')
            ->with('setDefault', [['new_option']]);

        $attribute->expects($this->at(2))
            ->method('__call')
            ->with(
                'setOption',
                [
                    [
                        'value' => ['new_option' => ['label', 42 => 'st 42']],
                        'order' => ['new_option' => 10],
                    ]
                ]
            );

        $attribute->expects($this->any())
            ->method('save');

        $option = $this->getMockBuilder('Magento\Catalog\Service\V1\Data\Eav\Option')
            ->disableOriginalConstructor()->getMock();

        $option->expects($this->any())
            ->method('getLabel')->will($this->returnValue('label'));

        $option->expects($this->any())
            ->method('getStoreLabels')
            ->will(
                $this->returnValue(
                    [
                        $label
                    ]
                )
            );

        $option->expects($this->any())
            ->method('getOrder')->will($this->returnValue(10));

        $option->expects($this->any())
            ->method('isDefault')->will($this->returnValue(true));

        $eavConfig = $this->getMockBuilder('Magento\Eav\Model\Config')
            ->disableOriginalConstructor()->getMock();

        $eavConfig->expects($this->any())
            ->method('getAttribute')
            ->with(
                \Magento\Catalog\Service\V1\ProductMetadataServiceInterface::ENTITY_TYPE_PRODUCT,
                $attributeCode
            )->will($this->returnValue($attribute));

        /** @var \Magento\Catalog\Service\V1\ProductAttributeServiceInterface $service */
        $service = $objectManager->getObject(
            'Magento\Catalog\Service\V1\ProductAttributeService',
            array(
                'eavConfig' => $eavConfig
            )
        );

        $this->assertTrue($service->addOption($attributeCode, $option));
    }
}
