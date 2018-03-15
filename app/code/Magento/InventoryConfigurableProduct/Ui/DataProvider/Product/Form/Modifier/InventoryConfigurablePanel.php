<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurablePanel;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryCatalog\Model\IsSingleSourceModeInterface;
use Magento\Ui\Component\Form;

/**
 * Data provider for Configurable panel.
 */
class InventoryConfigurablePanel extends AbstractModifier
{
    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private $searchCriteriaBuilderFactory;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var LocatorInterface
     */
    private $locator;


    /**
     * @param SourceItemRepositoryInterface $sourceItemRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param SourceRepositoryInterface $sourceRepository
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param LocatorInterface $locator
     */
    public function __construct(
        SourceItemRepositoryInterface $sourceItemRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        SourceRepositoryInterface $sourceRepository,
        IsSingleSourceModeInterface $isSingleSourceMode,
        LocatorInterface $locator
    ) {
        $this->sourceItemRepository = $sourceItemRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->sourceRepository = $sourceRepository;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->locator = $locator;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        $productId = $this->locator->getProduct()->getId();
        if (isset($data[$productId][ConfigurablePanel::CONFIGURABLE_MATRIX])) {
            foreach ($data[$productId][ConfigurablePanel::CONFIGURABLE_MATRIX] as $key => $productArray) {
                $qtyPerSource = $this->getQuantityPerSource($productArray[ProductInterface::SKU]);
                $data[$productId][ConfigurablePanel::CONFIGURABLE_MATRIX][$key]['qty_per_source'] = $qtyPerSource;
            }
        }

        return $data;
    }

    /**
     * Composes configuration for "quantity_per_source_container" component.
     *
     * @return array
     */
    private function getQuantityContainerConfig(): array
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'componentType' => 'text',
                        'component' => 'Magento_InventoryConfigurableProduct/js/form/element/quantity',
                        'template' => 'ui/form/field',
                        'dataScope' => 'qty_per_source',
                        'label' => __('Quantity Per Source'),
                        'formElement' => Form\Element\Input::NAME,
                        'imports' => [
                            'visible' => '!${$.provider}:${$.parentScope}.canEdit'
                        ],
                        'visibleIfCanEdit' => true,
                    ]
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        if ($this->isSingleSourceMode->execute() === false) {
            $meta[ConfigurablePanel::GROUP_CONFIGURABLE]['children']
            [ConfigurablePanel::CONFIGURABLE_MATRIX]['children']
            ['record']['children']['quantity_per_source_container'] = $this->getQuantityContainerConfig();

            unset($meta[ConfigurablePanel::GROUP_CONFIGURABLE]['children']
                [ConfigurablePanel::CONFIGURABLE_MATRIX]['children']
                ['record']['children']['quantity_container']);
        }

        return $meta;
    }

    /**
     * @param string $sku
     *
     * @return array
     */
    private function getQuantityPerSource(string $sku): array
    {
        $formSourceItems = [];

        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->addFilter(SourceItemInterface::SKU, $sku)->create();
        $sourceItems = $this->sourceItemRepository->getList($searchCriteria)->getItems();

        foreach ($sourceItems as $sourceItem) {
            $source = $this->sourceRepository->get($sourceItem->getSourceCode());

            $formSourceItems[] = [
                SourceItemInterface::SOURCE_CODE => $sourceItem->getSourceCode(),
                SourceItemInterface::QUANTITY => $sourceItem->getQuantity(),
                'source' => $source->getName(),
            ];
        }

        return $formSourceItems;
    }
}
