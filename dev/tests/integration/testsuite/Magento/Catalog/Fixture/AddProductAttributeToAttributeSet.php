<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Fixture;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
use Magento\TestFramework\Fixture\DataFixtureInterface;

/**
 * Add product attribute to attribute set fixture
 */
class AddProductAttributeToAttributeSet implements DataFixtureInterface
{
    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @param EavSetup $eavSetup
     * @param Config $eavConfig
     */
    public function __construct(
        EavSetup $eavSetup,
        Config $eavConfig
    ) {
        $this->eavSetup = $eavSetup;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @inheritdoc
     */
    public function apply(array $data = []): ?array
    {
        $attributeSetId = $data['attribute_set_id'] ?? $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');
        $groupId = $data['group_id'] ?? $this->eavSetup->getDefaultAttributeGroupId(Product::ENTITY, $attributeSetId);
        $attributeCode = $data['attribute_code'] ?? 'fixture_attribute';
        $this->eavSetup->addAttributeToGroup(Product::ENTITY, $attributeSetId, $groupId, $attributeCode);
        $this->eavConfig->clear();

        return [];
    }
}
