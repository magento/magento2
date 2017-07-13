<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model;

class SwatchAttributeCodesTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \Magento\Swatches\Model\SwatchAttributeCodes */
    private $swatchAttributeCodes;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->swatchAttributeCodes = $this->objectManager->create(
            \Magento\Swatches\Model\SwatchAttributeCodes::class
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Swatches/_files/swatch_attribute.php
     */
    public function testGetCodes()
    {
        $attribute = $this->objectManager
            ->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->load('color_swatch', 'attribute_code');
        $expected = [
            $attribute->getAttributeId() => $attribute->getAttributeCode()
        ];
        $swatchAttributeCodes = $this->swatchAttributeCodes->getCodes();

        $this->assertEquals($expected, $swatchAttributeCodes);
    }
}
