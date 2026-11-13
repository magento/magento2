<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Save;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Test\Fixture\Attribute as AttributeFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\SelectAttribute as SelectAttributeFixture;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\TestCase\AbstractBackendController;

#[
    AppArea('adminhtml'),
    DbIsolation(true),
]
class EmptyUserDefinedAttributeTest extends AbstractBackendController
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductResource
     */
    private $productResource;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->productResource = $this->_objectManager->get(ProductResource::class);
    }

    #[
        DataFixture(SelectAttributeFixture::class, as: 'attribute'),
        DataFixture(ProductFixture::class, ['sku' => 'simple'], 'product'),
    ]
    public function testEmptyValueWithoutDefaultIsNotStored(): void
    {
        $attribute = $this->fixtures->get('attribute');

        $this->dispatchSaveRequest($attribute->getAttributeCode());

        $this->assertSame([], $this->fetchStoredValues($attribute));
    }

    #[
        DataFixture(
            AttributeFixture::class,
            ['backend_type' => 'decimal', 'frontend_input' => 'price', 'default_value' => '7'],
            'attribute'
        ),
        DataFixture(ProductFixture::class, ['sku' => 'simple'], 'product'),
    ]
    public function testEmptyValueWithDefaultIsNotReplacedByDefault(): void
    {
        $attribute = $this->fixtures->get('attribute');

        $this->dispatchSaveRequest($attribute->getAttributeCode());

        $this->assertNull(
            $this->productRepository->get('simple', false, null, true)->getData($attribute->getAttributeCode())
        );
    }

    /**
     * Save the fixture product through the admin form with the given attribute left empty
     *
     * @param string $attributeCode
     * @return void
     */
    private function dispatchSaveRequest(string $attributeCode): void
    {
        $product = $this->productRepository->get('simple');
        $data = $product->getData();
        unset($data['entity_id'], $data['category_ids']);
        $data[$attributeCode] = '';

        $this->getRequest()->setPostValue(['product' => $data]);
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->dispatch('backend/catalog/product/save/id/' . $product->getId());
        $this->assertSessionMessages($this->equalTo(['You saved the product.']), MessageInterface::TYPE_SUCCESS);
    }

    /**
     * Fetch the rows stored for the attribute of the fixture product
     *
     * @param AbstractAttribute $attribute
     * @return array
     */
    private function fetchStoredValues(AbstractAttribute $attribute): array
    {
        $product = $this->productRepository->get('simple');
        $linkField = $this->productResource->getLinkField();
        $connection = $this->productResource->getConnection();
        $select = $connection->select()
            ->from($attribute->getBackend()->getTable())
            ->where('attribute_id = ?', $attribute->getAttributeId())
            ->where($linkField . ' = ?', $product->getData($linkField));

        return $connection->fetchAll($select);
    }
}
