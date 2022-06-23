<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Model\Attribute\Set;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\SetRepository;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Eav\Model\ResourceModel\GetEntityIdByAttributeId;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Weee\Model\ResourceModel\Tax as WeeTaxResource;
use PHPUnit\Framework\TestCase;

/**
 * Test checks that wee attributes data was deleted after unassigning wee attributes from attribute set.
 *
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/Catalog/_files/product_simple_duplicated.php
 * @magentoDataFixture Magento/Weee/_files/product_with_fpt.php
 */
class SaveTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Type
     */
    private $productEntityType;

    /**
     * @var GetEntityIdByAttributeId
     */
    private $getEntityIdByAttributeId;

    /**
     * @var SetRepository
     */
    private $setRepository;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var WeeTaxResource
     */
    private $taxResource;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->productEntityType = $this->objectManager->get(Type::class)
            ->loadByCode(ProductAttributeInterface::ENTITY_TYPE_CODE);
        $this->getEntityIdByAttributeId = $this->objectManager->get(GetEntityIdByAttributeId::class);
        $this->setRepository = $this->objectManager->get(SetRepository::class);
        $this->eavConfig = $this->objectManager->get(Config::class);
        $this->taxResource = $this->objectManager->get(WeeTaxResource::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
    }

    /**
     * @return void
     */
    public function testSaveAttributeSet(): void
    {
        $fptAttribute = $this->eavConfig->getAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE, 'fpt_for_all');
        $attributeSet = $this->setRepository->get($this->productEntityType->getDefaultAttributeSetId());
        $entityAttributeId = $this->getEntityIdByAttributeId->execute(
            (int)$attributeSet->getAttributeSetId(),
            (int)$fptAttribute->getAttributeId(),
            (int)$attributeSet->getDefaultGroupId($attributeSet->getAttributeSetId())
        );
        $attributeSet->organizeData(['attribute_set_name' => 'Default', 'not_attributes' => [$entityAttributeId]]);
        $this->setRepository->save($attributeSet);
        $this->assertEmpty(
            $this->getWeeTaxDataByAttributeAndProduct(
                (int)$this->productRepository->get('simple-with-ftp')->getId(),
                (int)$fptAttribute->getAttributeId()
            )
        );
    }

    /**
     * Loads data from wee_tax table.
     *
     * @param int $productId
     * @param int $attributeId
     * @return array
     */
    private function getWeeTaxDataByAttributeAndProduct(int $productId, int $attributeId): array
    {
        $select = $this->taxResource->getConnection()
            ->select()
            ->from(['main' => $this->taxResource->getMainTable()])
            ->where('entity_id = ?', $productId)
            ->where('attribute_id = ?', $attributeId);

        return $this->taxResource->getConnection()->fetchAll($select);
    }
}
