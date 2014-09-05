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
namespace Magento\Catalog\Service\V1\Product\Attribute;

use Magento\Catalog\Service\V1\Product\MetadataService;
use Magento\Framework\Service\V1\Data\SearchCriteriaBuilder;
use Magento\Catalog\Service\V1\Product\MetadataServiceInterface as ProductMetadataServiceInterface;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for retrieving product attributes types
     */
    public function testTypes()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $inputTypeFactoryMock = $this->getMock(
            'Magento\Catalog\Model\Product\Attribute\Source\InputtypeFactory',
            array('create')
        );
        $inputTypeFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue(
                $objectManager->getObject('Magento\Catalog\Model\Product\Attribute\Source\Inputtype')
            ));

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $attributeTypeBuilder = $helper->getObject(
            '\Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\TypeBuilder'
        );
        $productAttributeReadService = $objectManager->getObject(
            '\Magento\Catalog\Service\V1\Product\Attribute\ReadService',
            [
                'inputTypeFactory' => $inputTypeFactoryMock,
                'attributeTypeBuilder' => $attributeTypeBuilder
            ]
        );
        $types = $productAttributeReadService->types();
        $this->assertTrue(is_array($types));
        $this->assertNotEmpty($types);
        $this->assertInstanceOf('Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\Type', current($types));
    }

    /**
     * Test for retrieving product attribute
     */
    public function testInfo()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $attributeCode = 'attr_code';
        $metadataServiceMock = $this->getMock(
            'Magento\Catalog\Service\V1\MetadataService', array('getAttributeMetadata'),
            array(),
            '',
            false
        );
        $metadataServiceMock->expects($this->once())
            ->method('getAttributeMetadata')
            ->with(
                ProductMetadataServiceInterface::ENTITY_TYPE,
                $attributeCode
            );

        $typeBuilder = $objectManager->getObject(
            '\Magento\Catalog\Service\V1\Data\Eav\Product\Attribute\TypeBuilder',
            ['metadataService' => $objectManager->getObject('Magento\Framework\Service\Config\MetadataConfig')]
        );

        /** @var \Magento\Catalog\Service\V1\Product\Attribute\ReadServiceInterface $service */
        $service = $objectManager->getObject(
            'Magento\Catalog\Service\V1\Product\Attribute\ReadService',
            array(
                'metadataService' => $metadataServiceMock,
                'attributeTypeBuilder' => $typeBuilder
            )
        );
        $service->info($attributeCode);
    }


}
