<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Fixture;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Webapi\ServiceInputProcessor;
use Magento\TestFramework\Fixture\AbstractApiDataFixture;
use Magento\TestFramework\Fixture\ApiDataFixtureInterface;

/**
 * Add product attribute to attribute set fixture
 */
class AddProductAttributeToAttributeSet extends AbstractApiDataFixture
{
    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ServiceInputProcessor $serviceInputProcessor
     * @param EavSetup $eavSetup
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ServiceInputProcessor $serviceInputProcessor,
        EavSetup $eavSetup
    ) {
        parent::__construct($objectManager, $serviceInputProcessor);
        $this->eavSetup = $eavSetup;
    }

    /**
     * @inheritdoc
     */
    public function getService(): array
    {
        return [
            ApiDataFixtureInterface::SERVICE_CLASS => \Magento\Catalog\Api\ProductAttributeManagementInterface::class,
            ApiDataFixtureInterface::SERVICE_METHOD => 'assign',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getRollbackService(): array
    {
        return [
            ApiDataFixtureInterface::SERVICE_CLASS => \Magento\Catalog\Api\ProductAttributeManagementInterface::class,
            ApiDataFixtureInterface::SERVICE_METHOD => 'unassign',
        ];
    }

    /**
     * @inheritdoc
     */
    public function processServiceMethodParameters(array $data): array
    {
        $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');
        $attributeGroupId = $this->eavSetup->getDefaultAttributeGroupId(Product::ENTITY, $attributeSetId);
        $default = [
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
            'attribute_code' => 'fixture_attribute',
            'sort_order' => 0,
        ];
        return array_merge(
            $default,
            $data
        );
    }

    /**
     * @inheritdoc
     */
    public function processRollbackServiceMethodParameters(array $data): array
    {
        return [
            'attribute_set_id' => $data['attribute_set_id'],
            'attribute_code' => $data['attribute_code'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function processServiceResult(array $data, $result): array
    {
        return $data;
    }
}
