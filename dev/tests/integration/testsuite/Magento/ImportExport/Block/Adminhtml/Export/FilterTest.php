<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Test class for \Magento\ImportExport\Block\Adminhtml\Export\Filter
 */
namespace Magento\ImportExport\Block\Adminhtml\Export;

use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\DataObject;
use Magento\Framework\View\LayoutInterface;
use Magento\ImportExport\Model\Export\MandatoryAttributesProvider;
use Magento\ImportExport\Model\ResourceModel\Export\AttributeGridCollection;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FilterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testGetDateFromToHtmlWithValue()
    {
        Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        Bootstrap::getObjectManager()->get(\Magento\Framework\View\DesignInterface::class)
            ->setDefaultDesignTheme();
        $block = Bootstrap::getObjectManager()
            ->create(\Magento\ImportExport\Block\Adminhtml\Export\Filter::class);
        $method = new \ReflectionMethod(
            \Magento\ImportExport\Block\Adminhtml\Export\Filter::class,
            '_getDateFromToHtmlWithValue'
        );
        $arguments = [
            'data' => [
                'attribute_code' => 'date',
                'backend_type' => 'datetime',
                'frontend_input' => 'date',
                'frontend_label' => 'Date',
            ],
        ];
        $attribute = Bootstrap::getObjectManager()->create(
            \Magento\Eav\Model\Entity\Attribute::class,
            $arguments
        );
        $html = $method->invoke($block, $attribute, null);
        $this->assertNotEmpty($html);

        $dateFormat = Bootstrap::getObjectManager()->get(
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class
        )->getDateFormat(
            \IntlDateFormatter::SHORT
        );
        $pieces = array_filter(explode('<strong>', $html));
        foreach ($pieces as $piece) {
            $this->assertStringContainsString('dateFormat: "' . $dateFormat . '",', $piece);
        }
    }

    /**
     * Asserts that after loading the collection the 'skip' column's disabled_values
     * contains the numeric attribute_id of a mandatory EAV attribute ('sku').
     *
     * @magentoAppIsolation enabled
     */
    public function testSkipColumnDisabledValuesContainsMandatoryAttributeId(): void
    {
        Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\View\DesignInterface::class)
            ->setDefaultDesignTheme();

        $mandatoryAttributesProvider = $objectManager->create(
            MandatoryAttributesProvider::class,
            ['mandatoryEavAttributes' => ['sku']]
        );

        // Use layout->createBlock() so the block gets a name registered in the layout structure.
        // Without it, addColumn() calls setAsChild() with a null parent, triggering a PHP deprecation.
        /** @var LayoutInterface $layout */
        $layout = $objectManager->get(LayoutInterface::class);
        /** @var Filter $block */
        $block = $layout->createBlock(
            Filter::class,
            'export.filter',
            ['mandatoryAttributesProvider' => $mandatoryAttributesProvider]
        );

        $skuAttributeId = 71;
        $skuItem = new DataObject([
            'attribute_code' => 'sku',
            'attribute_id'   => $skuAttributeId,
            'frontend_label' => 'SKU',
        ]);
        $skuItem->setId($skuAttributeId);

        /** @var AttributeGridCollection $collection */
        $collection = $objectManager->create(AttributeGridCollection::class);
        $collection->setItems([$skuItem]);
        $collection->setOrder('attribute_code', DataCollection::SORT_ORDER_ASC);
        $block->setCollection($collection);

        // _prepareColumns() must run first so the 'skip' column exists when _afterLoadCollection() fires.
        (new \ReflectionMethod(Filter::class, '_prepareColumns'))->invoke($block);
        (new \ReflectionMethod(Filter::class, '_afterLoadCollection'))->invoke($block);

        $skipColumn = $block->getColumn('skip');
        $this->assertNotNull($skipColumn, 'The "skip" column must exist on the block after _prepareColumns().');

        $disabledValues = $skipColumn->getData('disabled_values');
        $this->assertIsArray($disabledValues, 'The "skip" column\'s disabled_values must be an array.');
        $this->assertContains(
            $skuAttributeId,
            $disabledValues,
            'The numeric attribute_id of the mandatory "sku" attribute must appear in the '
            . '"skip" column\'s disabled_values.'
        );
    }

    /**
     * Asserts that after loading the collection the frontend_label of a mandatory EAV attribute
     * ('sku') has been appended with the '[Mandatory]' tag.
     *
     * @magentoAppIsolation enabled
     */
    public function testMandatoryAttributeFrontendLabelContainsMandatoryTag(): void
    {
        Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\View\DesignInterface::class)
            ->setDefaultDesignTheme();

        $mandatoryAttributesProvider = $objectManager->create(
            MandatoryAttributesProvider::class,
            ['mandatoryEavAttributes' => ['sku']]
        );

        /** @var LayoutInterface $layout */
        $layout = $objectManager->get(LayoutInterface::class);
        /** @var Filter $block */
        $block = $layout->createBlock(
            Filter::class,
            'export.filter.label',
            ['mandatoryAttributesProvider' => $mandatoryAttributesProvider]
        );

        $skuAttributeId = 71;
        $skuItem = new DataObject([
            'attribute_code' => 'sku',
            'attribute_id'   => $skuAttributeId,
            'frontend_label' => 'SKU',
        ]);
        $skuItem->setId($skuAttributeId);

        /** @var AttributeGridCollection $collection */
        $collection = $objectManager->create(AttributeGridCollection::class);
        $collection->setItems([$skuItem]);
        $collection->setOrder('attribute_code', DataCollection::SORT_ORDER_ASC);
        $block->setCollection($collection);

        (new \ReflectionMethod(Filter::class, '_prepareColumns'))->invoke($block);
        (new \ReflectionMethod(Filter::class, '_afterLoadCollection'))->invoke($block);

        $skuItemAfter = null;
        foreach ($collection->getItems() as $item) {
            if ($item->getAttributeCode() === 'sku') {
                $skuItemAfter = $item;
                break;
            }
        }

        $this->assertNotNull($skuItemAfter, 'The "sku" item must remain present in the collection.');
        $this->assertStringContainsString(
            '[Mandatory]',
            $skuItemAfter->getFrontendLabel(),
            'The frontend_label for the mandatory "sku" attribute must contain "[Mandatory]" '
            . 'after _afterLoadCollection() runs.'
        );
    }
}
