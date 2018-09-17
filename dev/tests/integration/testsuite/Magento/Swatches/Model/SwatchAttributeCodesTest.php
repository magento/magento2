<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Model;

class SwatchAttributeCodesTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Swatches\Model\SwatchAttributeCodes */
    private $swatchAttributeCodes;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->swatchAttributeCodes = $this->_objectManager->create(
            \Magento\Swatches\Model\SwatchAttributeCodes::class
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Swatches/_files/swatch_attribute.php
     */
    public function testGetCodes()
    {
        $attribute = $this->_objectManager
            ->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->load('color_swatch', 'attribute_code');
        $expected = [
            $attribute->getAttributeId() => $attribute->getAttributeCode()
        ];
        $swatchAttributeCodes = $this->swatchAttributeCodes->getCodes();

        $this->assertEquals($expected, $swatchAttributeCodes);
    }
}
