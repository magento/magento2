<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Fixture;

use Magento\Catalog\Api\ProductAttributeManagementInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\DataObject;
use Magento\TestFramework\Fixture\Api\ServiceFactory;
use Magento\TestFramework\Fixture\DataFixtureInterface;

class AddAttributeToAttributeSet implements DataFixtureInterface
{
    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @param ServiceFactory $serviceFactory
     * @param EavSetup $eavSetup
     */
    public function __construct(
        ServiceFactory $serviceFactory,
        EavSetup $eavSetup
    ) {
        $this->serviceFactory = $serviceFactory;
        $this->eavSetup = $eavSetup;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'attribute_code'     => (string) Attribute Code. Required.
     *      'attribute_set_id'   => (int) Attribute Set ID. Optional. Default: default attribute set.
     *      'attribute_group_id' => (int) Attribute Group ID. Optional. Default: default attribute group.
     *      'sort_order'         => (int) Sort Order. Optional. Default: 0.
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');
        $attributeGroupId = $this->eavSetup->getDefaultAttributeGroupId(Product::ENTITY, $attributeSetId);
        $data = array_merge(
            [
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'sort_order' => 0,
            ],
            $data
        );

        $service = $this->serviceFactory->create(ProductAttributeManagementInterface::class, 'assign');
        $service->execute($data);

        return null;
    }
}
