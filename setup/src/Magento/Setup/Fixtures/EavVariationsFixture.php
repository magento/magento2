<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Fixtures;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Framework\App\CacheInterface;
use Magento\Store\Model\StoreManager;
use Magento\Swatches\Model\Swatch;

/**
 * Generate attributes default attribute set
 */
class EavVariationsFixture extends Fixture
{
    /**
     * @var int
     */
    protected $priority = 40;

    const ATTRIBUTE_SET_ID = 4;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var Set
     */
    private $attributeSet;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * EavVariationsFixture constructor.
     * @param FixtureModel $fixtureModel
     * @param Config $eavConfig
     * @param CacheInterface $cache
     * @param StoreManager $storeManager
     * @param Set $attributeSet
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(
        FixtureModel $fixtureModel,
        Config $eavConfig,
        CacheInterface $cache,
        StoreManager $storeManager,
        Set $attributeSet,
        AttributeFactory $attributeFactory
    ) {
        parent::__construct($fixtureModel);
        $this->eavConfig = $eavConfig;
        $this->cache = $cache;
        $this->storeManager = $storeManager;
        $this->attributeSet = $attributeSet;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->fixtureModel->getValue('configurable_products', [])
            || in_array($this->getAttributeCode(), $this->eavConfig->getEntityAttributeCodes(Product::ENTITY))) {
            return;
        }

        $this->generateAttribute($this->fixtureModel->getValue('configurable_products_variation', 3));

        $cacheKey = Config::ATTRIBUTES_CACHE_ID . Product::ENTITY;
        $this->cache->remove($cacheKey);
    }

    /**
     * {@inheritdoc}
     */
    public function getActionTitle()
    {
        return 'Generating configurable EAV variations';
    }

    /**
     * {@inheritdoc}
     */
    public function introduceParamLabels()
    {
        return [];
    }

    /**
     * @param int $optionCount
     * @return void
     */
    private function generateAttribute($optionCount)
    {
        $stores = $this->storeManager->getStores();
        $storeViewsCount = count($stores);
        $options = [];

        for ($option = 1; $option <= $optionCount; $option++) {
            $options['order']['option_' . $option] = $option;
            $options['value']['option_' . $option] = array_fill(0, $storeViewsCount + 1, 'option ' . $option);
            $options['delete']['option_' . $option] = '';
        }

        $data = [
            'frontend_label' => array_fill(0, $storeViewsCount + 1, 'configurable variations'),
            'frontend_input' => 'select',
            'is_required' => '0',
            'option' => $options,
            'default' => ['option_0'],
            'attribute_code' => $this->getAttributeCode(),
            'is_global' => '1',
            'default_value_text' => '',
            'default_value_yesno' => '0',
            'default_value_date' => '',
            'default_value_textarea' => '',
            'is_unique' => '0',
            'is_searchable' => '1',
            'is_visible_in_advanced_search' => '0',
            'is_comparable' => '0',
            'is_filterable' => '1',
            'is_filterable_in_search' => '0',
            'is_used_for_promo_rules' => '0',
            'is_html_allowed_on_front' => '1',
            'is_visible_on_front' => '0',
            'used_in_product_listing' => '0',
            'used_for_sort_by' => '0',
            'source_model' => null,
            'backend_model' => null,
            'apply_to' => [],
            'backend_type' => 'int',
            'entity_type_id' => 4,
            'is_user_defined' => 1,
        ];

        $data['swatch_input_type'] = Swatch::SWATCH_INPUT_TYPE_VISUAL;
        $data['swatchvisual']['value'] = array_reduce(
            range(1, $optionCount),
            function ($values, $index) use ($optionCount) {
                $values['option_' . $index] = '#'
                    . str_repeat(
                        dechex(255 * $index / $optionCount),
                        3
                    );
                return $values;
            },
            []
        );
        $data['optionvisual']['value'] = array_reduce(
            range(1, $optionCount),
            function ($values, $index) use ($optionCount) {
                $values['option_' . $index] = ['option ' . $index];
                return $values;
            },
            []
        );

        /**
         * The logic is not obvious, but looking to the controller logic for configurable products this attribute
         * requires to be saved twice to become a child of Default attribute set and become available for creating
         * and|or importing configurable products.
         * See MAGETWO-16492
         */
        $model = $this->attributeFactory->create(['data' => $data]);
        $attributeSet = $this->attributeSet->load(self::ATTRIBUTE_SET_ID);

        $model->setAttributeSetId(self::ATTRIBUTE_SET_ID);
        $model->setAttributeGroupId($attributeSet->getDefaultGroupId(4));
        $model->save();

        $model->setAttributeSetId(self::ATTRIBUTE_SET_ID);
        $model->save();
    }

    /**
     * @return string
     */
    private function getAttributeCode()
    {
        return 'configurable_variation';
    }
}
