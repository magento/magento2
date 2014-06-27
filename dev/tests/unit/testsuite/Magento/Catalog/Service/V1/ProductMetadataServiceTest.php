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

use Magento\Catalog\Service\V1\Data\Eav\AttributeMetadataBuilder;
use Magento\Catalog\Service\V1\Data\Eav\OptionBuilder;
use Magento\Catalog\Service\V1\Data\Eav\ValidationRuleBuilder;

class ProductMetadataServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test getAttributeMetadata
     */
    public function testGetAttributeMetadata()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $data = array(
            'attribute_id' => 1,
            'attribute_code' => 'description',
            'frontend_label' => 'English',
            'store_labels' => array(1 => 'France'),
            'frontend_input' => 'textarea',
        );

        //attributeMock
        $attributeMock = $this->getMock(
            'Magento\Framework\Object',
            array('usesSource', 'getSource', 'isScopeGlobal'),
            array('data' => $data)
        );
        $attributeMock->expects($this->any())->method('isScopeGlobal')->will($this->returnValue(true));
        $attributeMock->expects($this->any())->method('usesSource')->will($this->returnValue(true));
        $attributeMock->expects($this->any())->method('getSource')
            ->will($this->returnValue(new \Magento\Framework\Object()));

        // eavConfigMock
        $eavConfigMock = $this->getMock('Magento\Eav\Model\Config', array('getAttribute'), array(), '', false);
        $eavConfigMock->expects($this->any())->method('getAttribute')->will($this->returnValue($attributeMock));

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $validationRuleBuilder = $helper->getObject('\Magento\Catalog\Service\V1\Data\Eav\ValidationRuleBuilder');
        $optionBuilder = $helper->getObject('\Magento\Catalog\Service\V1\Data\Eav\OptionBuilder');
        $frontendLabelBuilder = $helper
            ->getObject('\Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\FrontendLabelBuilder');
        $attrMetadataBuilder = $objectManager->getObject(
            'Magento\Catalog\Service\V1\Data\Eav\AttributeMetadataBuilder',
            [
                'optionBuilder' => $optionBuilder,
                'validationRuleBuilder' => $validationRuleBuilder,
                'frontendLabelBuilder' => $frontendLabelBuilder,
            ]
        );

        // create service
        $service = $objectManager->getObject('Magento\Catalog\Service\V1\ProductMetadataService',
            array(
                'eavConfig' => $eavConfigMock,
                'attributeMetadataBuilder'
                    => $attrMetadataBuilder
            )
        );

        $dto = $service->getAttributeMetadata('entity_type', 'attr_code');
        $this->assertInstanceOf('Magento\Framework\Service\Data\AbstractObject', $dto);
        $this->assertEquals($attributeMock->getFrontendInput(), $dto->getFrontendInput());

        $this->assertEquals(0, $dto->getFrontendLabel()[0]->getStoreId());
        $this->assertEquals(1, $dto->getFrontendLabel()[1]->getStoreId());
        $this->assertEquals('English', $dto->getFrontendLabel()[0]->getLabel());
        $this->assertEquals('France', $dto->getFrontendLabel()[1]->getLabel());
    }
}
