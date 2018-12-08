<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Setup;

/**
 * Test class for Magento\Eav\Setup\EavSetup.
 * @magentoDbIsolation enabled
 */
class EavSetupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Eav setup.
     *
     * @var \Magento\Eav\Setup\EavSetup
     */
    private $eavSetup;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->eavSetup = $objectManager->create(\Magento\Eav\Setup\EavSetup::class);
    }

    /**
     * Verify that add attribute work correct attribute_code.
     *
     * @param string $attributeCode
     *
     * @dataProvider addAttributeDataProvider
     */
    public function testAddAttribute($attributeCode)
    {
        $attributeData = $this->getAttributeData();

        $this->eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, $attributeData);

        $attribute = $this->eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);

        $this->assertEmpty(array_diff($attributeData, $attribute));
    }

    /**
     * Data provider for testAddAttributeThrowException().
     *
     * @return array
     */
    public function addAttributeDataProvider()
    {
        return [
            ['eav_setup_test'],
            ['_59_characters_59_characters_59_characters_59_characters_59'],
        ];
    }

    /**
     * Verify that add attribute throw exception if attribute_code is not valid.
     *
     * @param string|null $attributeCode
     *
     * @dataProvider addAttributeThrowExceptionDataProvider
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage An attribute code must not be less than 1 and more than 60 characters.
     */
    public function testAddAttributeThrowException($attributeCode)
    {
        $attributeData = $this->getAttributeData();

        $this->eavSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, $attributeData);
    }

    /**
     * Data provider for testAddAttributeThrowException().
     *
     * @return array
     */
    public function addAttributeThrowExceptionDataProvider()
    {
        return [
            [null],
            [''],
            [' '],
            ['more_than_60_characters_more_than_more_than_60_characters_more'],
        ];
    }

    /**
     * Get simple attribute data.
     */
    private function getAttributeData()
    {
        $attributeData = [
            'type' => 'varchar',
            'backend' => '',
            'frontend' => '',
            'label' => 'Eav Setup Test',
            'input' => 'text',
            'class' => '',
            'source' => '',
            'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
            'visible' => 0,
            'required' => 0,
            'user_defined' => 1,
            'default' => 'none',
            'searchable' => 0,
            'filterable' => 0,
            'comparable' => 0,
            'visible_on_front' => 0,
            'unique' => 0,
            'apply_to' => 'category',
        ];

        return $attributeData;
    }
}
