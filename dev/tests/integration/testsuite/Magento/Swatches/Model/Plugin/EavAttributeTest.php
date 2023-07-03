<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Model\Plugin;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\ObjectManagerInterface;
use Magento\Swatches\Model\ResourceModel\Swatch as SwatchResource;
use Magento\Swatches\Model\Swatch;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Interception\PluginList;
use PHPUnit\Framework\TestCase;

/**
 * Checks swatches attribute save behaviour
 *
 * @see \Magento\Swatches\Model\Plugin\EavAttribute
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class EavAttributeTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var ProductAttributeRepositoryInterface */
    private $productAttributeRepository;

    /** @var SwatchResource */
    private $swatchResource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productAttributeRepository = $this->objectManager->get(ProductAttributeRepositoryInterface::class);
        $this->swatchResource = $this->objectManager->get(SwatchResource::class);
    }

    /**
     * @return void
     */
    public function testEavAttributePluginIsRegistered(): void
    {
        $pluginInfo = $this->objectManager->get(PluginList::class)->get(Attribute::class);
        $this->assertSame(EavAttribute::class, $pluginInfo['save_swatches_option_params']['instance']);
    }

    /**
     * @magentoDataFixture Magento/Swatches/_files/text_swatch_attribute.php
     *
     * @return void
     */
    public function testChangeAttributeToDropdown(): void
    {
        $attribute = $this->productAttributeRepository->get('test_configurable');
        $options = $attribute->getOptions();
        unset($options[0]);
        $optionsIds = $this->collectOptionsIds($options);
        $attribute->addData($this->prepareOptions($options));
        $attribute->setData(Swatch::SWATCH_INPUT_TYPE_KEY, Swatch::SWATCH_INPUT_TYPE_DROPDOWN);
        $attribute->beforeSave();
        $this->assertEmpty($this->fetchSwatchOptions($optionsIds), 'Swatch options were not deleted');
    }

    /**
     * Prepare options
     *
     * @param array $options
     * @return array
     */
    private function prepareOptions(array $options): array
    {
        foreach ($options as $key => $option) {
            $preparedOptions['option']['order'][$option->getValue()] = $key;
            $preparedOptions['option']['value'][$option->getValue()] = [$option->getLabel()];
            $preparedOptions['option']['delete'][$option->getValue()] = '';
        }

        return $preparedOptions ?? [];
    }

    /**
     * Collect options ids
     *
     * @param array $options
     * @return array
     */
    private function collectOptionsIds(array $options): array
    {
        foreach ($options as $option) {
            $optionsIds[] = $option->getValue();
        }

        return $optionsIds ?? [];
    }

    /**
     * Fetch related to provided ids records from eav_attribute_option_swatch table
     *
     * @param $optionsIds
     * @return array
     */
    private function fetchSwatchOptions(array $optionsIds): array
    {
        $connection = $this->swatchResource->getConnection();
        $select = $connection->select()->from(['main_table' => $this->swatchResource->getMainTable()])
            ->where('main_table.option_id IN (?)', $optionsIds);

        return $connection->fetchAll($select);
    }
}
