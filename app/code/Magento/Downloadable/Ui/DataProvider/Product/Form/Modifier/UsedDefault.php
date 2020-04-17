<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Form;

/**
 * Class for Product Form Modifier User Default
 */
class UsedDefault extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var scopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var array
     */
    protected $meta = [];

    /**
     * @param LocatorInterface $locator
     * @param ScopeConfigInterface $scopeConfig
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        LocatorInterface $locator,
        ScopeConfigInterface $scopeConfig,
        ArrayManager $arrayManager
    ) {
        $this->locator = $locator;
        $this->scopeConfig = $scopeConfig;
        $this->arrayManager = $arrayManager;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->titleUsedDefault('links_title')
            ->titleUsedDefault('samples_title')
            ->priceUsedDefault()
            ->titleUsedDefaultInGrid('link_title')
            ->titleUsedDefaultInGrid('sample_title');

        return $this->meta;
    }

    /**
     * Add default service to title
     *
     * @param string $titleIndex
     * @return $this
     */
    protected function titleUsedDefault($titleIndex)
    {
        $canDisplayService = $this->locator->getProduct()->getStoreId();
        $usedDefault = $this->locator->getProduct()->getAttributeDefaultValue($titleIndex) === false;
        if ($canDisplayService) {
            $useDefaultConfig = [
                'usedDefault' => $usedDefault,
                'disabled' => $usedDefault,
                'service' => [
                    'template' => 'ui/form/element/helper/service',
                ]
            ];
            $linksTitlePath = $this->arrayManager->findPath($titleIndex, $this->meta, null, 'children')
                . static::META_CONFIG_PATH;
            $this->meta = $this->arrayManager->merge($linksTitlePath, $this->meta, $useDefaultConfig);
        }

        return $this;
    }

    /**
     * Add default service to price in grid
     *
     * @return $this
     */
    protected function priceUsedDefault()
    {
        $scope = (int)$this->scopeConfig->getValue(
            \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($scope == \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE && $this->locator->getProduct()->getStoreId()) {
            $linkPricePath = $this->arrayManager->findPath('container_link_price', $this->meta, null, 'children');
            $checkboxPath = $linkPricePath . '/children/use_default_price/arguments/data/config';
            $useDefaultConfig = [
                'componentType' => Form\Element\Checkbox::NAME,
                'formElement' => Form\Field::NAME,
                'component' => 'Magento_Downloadable/js/components/use-price-default-handler',
                'description' => __('Use Default Value'),
                'dataScope' => 'use_default_price',
                'valueMap' => [
                    'false' => '0',
                    'true' => '1',
                ],
                'imports' => [
                    'linksPurchasedSeparately' => '${$.provider}:data.product.links_purchased_separately',
                    '__disableTmpl' => ['linksPurchasedSeparately' => false],
                ],
            ];
            $this->meta = $this->arrayManager->set($checkboxPath, $this->meta, $useDefaultConfig);
        }

        return $this;
    }

    /**
     * Add use default checkbox to title in grid
     *
     * @param string $indexTitle
     * @return $this
     */
    protected function titleUsedDefaultInGrid($indexTitle)
    {
        if ($this->locator->getProduct()->getStoreId()) {
            $linkTitleGroupPath = $this->arrayManager->findPath(
                'container_' . $indexTitle,
                $this->meta,
                null,
                'children'
            );
            $checkboxPath = $linkTitleGroupPath . '/children/use_default_title/arguments/data/config';
            $useDefaultConfig = [
                'componentType' => Form\Element\Checkbox::NAME,
                'formElement' => Form\Field::NAME,
                'description' => __('Use Default Value'),
                'dataScope' => 'use_default_title',
                'valueMap' => [
                    'false' => '0',
                    'true' => '1',
                ],
                'exports' => [
                    'checked' => '${$.parentName}.' . $indexTitle . ':disabled',
                    '__disableTmpl' => ['checked' => false],
                ],
            ];
            $this->meta = $this->arrayManager->set($checkboxPath, $this->meta, $useDefaultConfig);
        }

        return $this;
    }
}
