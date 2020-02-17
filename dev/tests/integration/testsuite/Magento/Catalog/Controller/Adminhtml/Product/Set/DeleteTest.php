<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Set;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Eav\Model\GetAttributeSetByName;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test for attribute set deleting.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class DeleteTest extends AbstractBackendController
{
    /**
     * @var GetAttributeSetByName
     */
    private $getAttributeSetByName;

    /**
     * @var ProductInterface|Product
     */
    private $product;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->getAttributeSetByName = $this->_objectManager->get(GetAttributeSetByName::class);
        $this->product = $this->_objectManager->get(ProductInterface::class);
        $this->attributeSetRepository = $this->_objectManager->get(AttributeSetRepositoryInterface::class);
        $this->escaper = $this->_objectManager->get(Escaper::class);
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
    }

    /**
     * Assert that default attribute set is not deleted.
     *
     * @return void
     */
    public function testDefaultAttributeSetIsNotDeleted(): void
    {
        $productDefaultAttrSetId = (int)$this->product->getDefaultAttributeSetId();
        $this->performDeleteAttributeSetRequest($productDefaultAttrSetId);
        $expectedSessionMessage = $this->escaper->escapeHtml((string)__('We can\'t delete this set right now.'));
        $this->assertSessionMessages(
            $this->equalTo([$expectedSessionMessage]),
            MessageInterface::TYPE_ERROR
        );
        try {
            $this->attributeSetRepository->get($productDefaultAttrSetId);
        } catch (NoSuchEntityException $e) {
            $this->fail(sprintf('Default attribute set was deleted. Message: %s', $e->getMessage()));
        }
    }

    /**
     * Assert that custom attribute set deleting properly.
     *
     * @magentoDataFixture Magento/Eav/_files/empty_attribute_set.php
     *
     * @return void
     */
    public function testDeleteCustomAttributeSetById(): void
    {
        $this->deleteAttributeSetByNameAndAssert('empty_attribute_set');
    }

    /**
     * Assert that product will be deleted if delete attribute set which the product is attached.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_with_test_attribute_set.php
     *
     * @return void
     */
    public function testProductIsDeletedAfterDeleteItsAttributeSet(): void
    {
        $this->deleteAttributeSetByNameAndAssert('new_attribute_set');
        $this->expectExceptionObject(
            new NoSuchEntityException(
                __('The product that was requested doesn\'t exist. Verify the product and try again.')
            )
        );
        $this->productRepository->get('simple');
    }

    /**
     * Perform request to delete attribute set and assert that attribute set is deleted.
     *
     * @param string $attributeSetName
     * @return void
     */
    private function deleteAttributeSetByNameAndAssert(string $attributeSetName): void
    {
        $attributeSet = $this->getAttributeSetByName->execute($attributeSetName);
        $this->performDeleteAttributeSetRequest((int)$attributeSet->getAttributeSetId());
        $this->assertSessionMessages(
            $this->equalTo([(string)__('The attribute set has been removed.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertNull($this->getAttributeSetByName->execute($attributeSetName));
    }

    /**
     * Perform "catalog/product_set/delete" controller dispatch.
     *
     * @param int $attributeSetId
     * @return void
     */
    private function performDeleteAttributeSetRequest(int $attributeSetId): void
    {
        $this->getRequest()
            ->setParam('id', $attributeSetId)
            ->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/catalog/product_set/delete/');
    }
}
