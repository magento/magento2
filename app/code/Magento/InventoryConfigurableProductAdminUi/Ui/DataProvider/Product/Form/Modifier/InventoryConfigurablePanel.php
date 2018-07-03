<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProductAdminUi\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\ConfigurablePanel;
use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryConfigurableProductAdminUi\Model\GetQuantityInformationPerSource;
use Magento\Ui\Component\Form;

/**
 * Data provider for Configurable panel.
 */
class InventoryConfigurablePanel extends AbstractModifier
{
    /**
     * @var GetQuantityInformationPerSource
     */
    private $getQuantityInformationPerSource;

    /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @param GetQuantityInformationPerSource $getQuantityInformationPerSource
     * @param IsSingleSourceModeInterface $isSingleSourceMode
     * @param LocatorInterface $locator
     */
    public function __construct(
        GetQuantityInformationPerSource $getQuantityInformationPerSource,
        IsSingleSourceModeInterface $isSingleSourceMode,
        LocatorInterface $locator
    ) {
        $this->getQuantityInformationPerSource = $getQuantityInformationPerSource;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->locator = $locator;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        if ($this->isSingleSourceMode->execute() === false) {
            $productId = $this->locator->getProduct()->getId();

            if (isset($data[$productId][ConfigurablePanel::CONFIGURABLE_MATRIX])) {
                foreach ($data[$productId][ConfigurablePanel::CONFIGURABLE_MATRIX] as $key => $productArray) {
                    $quantityPerSource
                        = $this->getQuantityInformationPerSource->execute($productArray[ProductInterface::SKU]);
                    $data[$productId][ConfigurablePanel::CONFIGURABLE_MATRIX][$key]['quantity_per_source']
                        = $quantityPerSource;
                }
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
                        'component' =>
                            'Magento_InventoryConfigurableProductAdminUi/js/form/element/quantity-per-source',
                        'template' => 'ui/form/field',
                        'dataScope' => 'quantity_per_source',
                        'label' => __('Quantity Per Source'),
                        'formElement' => Form\Element\Input::NAME,
                    ],
                ],
            ],
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

            $meta[ConfigurablePanel::GROUP_CONFIGURABLE]['children']
            [ConfigurablePanel::CONFIGURABLE_MATRIX]['arguments']['data']['config']['component']
                = 'Magento_InventoryConfigurableProductAdminUi/js/components/dynamic-rows-configurable';
        }

        return $meta;
    }
}
