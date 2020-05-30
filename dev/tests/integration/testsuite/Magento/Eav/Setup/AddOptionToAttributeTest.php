<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests coverage for \Magento\Eav\Setup\AddOptionToAttribute
 */
class AddOptionToAttributeTest extends TestCase
{
    /**
     * @var AddOptionToAttribute
     */
    private $operation;

    /**
     * @var int
     */
    private $attributeId;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attrRepo;

    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    protected function setUp(): void
    {
        $objectManager = ObjectManager::getInstance();

        $this->operation = $objectManager->get(AddOptionToAttribute::class);
        /** @var ModuleDataSetupInterface $setup */
        $this->setup = $objectManager->get(ModuleDataSetupInterface::class);
        /** @var AttributeRepositoryInterface attrRepo */
        $this->attrRepo = $objectManager->get(AttributeRepositoryInterface::class);
        /** @var EavSetup $eavSetup */
        $this->eavSetup = $objectManager->get(EavSetupFactory::class)
                                        ->create(['setup' => $this->setup]);
        $this->attributeId = $this->eavSetup->getAttributeId(Product::ENTITY, 'zzz');
    }

    /**
     * @param bool $fetchPairs
     *
     * @return array
     */
    private function getAttributeOptions($fetchPairs = true): array
    {
        $optionTable = $this->setup->getTable('eav_attribute_option');
        $optionValueTable = $this->setup->getTable('eav_attribute_option_value');

        $select = $this->setup
            ->getConnection()
            ->select()
            ->from(['o' => $optionTable])
            ->reset('columns')
            ->columns('sort_order')
            ->join(['ov' => $optionValueTable], 'o.option_id = ov.option_id', 'value')
            ->where(AttributeInterface::ATTRIBUTE_ID . ' = ?', $this->attributeId)
            ->where('store_id = 0');

        return $fetchPairs
            ? $this->setup->getConnection()->fetchPairs($select)
            : $this->setup->getConnection()->fetchAll($select);
    }

    /**
     * @magentoDataFixture Magento/Eav/_files/attribute_with_options.php
     */
    public function testAddNewOptions()
    {
        $optionsBefore = $this->getAttributeOptions(false);
        $this->operation->execute(
            [
                'values' => ['new1', 'new2'],
                'attribute_id' => $this->attributeId
            ]
        );
        $optionsAfter = $this->getAttributeOptions(false);
        $this->assertEquals(count($optionsBefore) + 2, count($optionsAfter));
        foreach ($optionsBefore as $option) {
            $this->assertContainsEquals($option, $optionsAfter);
        }
    }

    /**
     * @magentoDataFixture Magento/Eav/_files/attribute_with_options.php
     */
    public function testAddExistingOptionsWithTheSameSortOrder()
    {
        $optionsBefore = $this->getAttributeOptions();
        $this->operation->execute(
            [
                'values' => ['Black', 'White'],
                'attribute_id' => $this->attributeId
            ]
        );
        $optionsAfter = $this->getAttributeOptions();
        $this->assertEquals(count($optionsBefore), count($optionsAfter));
        foreach ($optionsBefore as $option) {
            $this->assertContainsEquals($option, $optionsAfter);
        }
    }

    /**
     * @magentoDataFixture Magento/Eav/_files/attribute_with_options.php
     */
    public function testAddExistingOptionsWithDifferentSortOrder()
    {
        $optionsBefore = $this->getAttributeOptions();
        $this->operation->execute(
            [
                'values' => [666 => 'White', 777 => 'Black'],
                'attribute_id' => $this->attributeId
            ]
        );
        $optionsAfter = $this->getAttributeOptions();
        $this->assertSameSize($optionsBefore, array_intersect($optionsBefore, $optionsAfter));
        $this->assertEquals($optionsAfter[777], $optionsBefore[0]);
        $this->assertEquals($optionsAfter[666], $optionsBefore[1]);
    }

    /**
     * @magentoDataFixture Magento/Eav/_files/attribute_with_options.php
     */
    public function testAddMixedOptions()
    {
        $sizeBefore = count($this->getAttributeOptions());
        $this->operation->execute(
            [
                'values' => [666 => 'Black', 'NewOption'],
                'attribute_id' => $this->attributeId
            ]
        );
        $updatedOptions = $this->getAttributeOptions();
        $this->assertEquals(count($updatedOptions), $sizeBefore + 1);
        $this->assertEquals($updatedOptions[666], 'Black');
        $this->assertEquals($updatedOptions[667], 'NewOption');
    }

    /**
     * @magentoDataFixture Magento/Eav/_files/attribute_with_options.php
     */
    public function testAddNewOption()
    {
        $sizeBefore = count($this->getAttributeOptions());

        $this->operation->execute(
            [
                'attribute_id' => $this->attributeId,
                'order' => [0 => 13],
                'value' => [
                    [
                        0 => 'NewOption',
                    ],
                ],
            ]
        );
        $updatedOptions = $this->getAttributeOptions();
        $this->assertEquals(count($updatedOptions), $sizeBefore + 1);
        $this->assertEquals($updatedOptions[13], 'NewOption');
    }

    /**
     * @magentoDataFixture Magento/Eav/_files/attribute_with_options.php
     */
    public function testDeleteOption()
    {
        $optionsBefore = $this->getAttributeOptions();
        $options = $this->attrRepo->get(Product::ENTITY, $this->attributeId)->getOptions();
        /** @var AttributeOptionInterface $optionToDelete */
        $optionToDelete = end($options);
        $this->operation->execute(
            [
                'attribute_id' => $this->attributeId,
                'delete' => [$optionToDelete->getValue() => true],
                'value' => [
                    $optionToDelete->getValue() => null,
                ],
            ]
        );
        $updatedOptions = $this->getAttributeOptions();
        $this->assertEquals(count($updatedOptions), count($optionsBefore) - 1);
        foreach ($updatedOptions as $option) {
            $this->assertContainsEquals($option, $optionsBefore);
        }
    }

    /**
     * @magentoDataFixture Magento/Eav/_files/attribute_with_options.php
     */
    public function testUpdateOption()
    {
        $optionsBefore = $this->getAttributeOptions();
        $this->operation->execute(
            [
                'attribute_id' => $this->attributeId,
                'value' => [
                    0 => ['updatedValue'],
                ],
            ]
        );
        $optionsAfter = $this->getAttributeOptions();
        $this->assertEquals($optionsAfter[0], 'updatedValue');
        $this->assertSame(array_slice($optionsBefore, 1), array_slice($optionsAfter, 1));
    }
}
