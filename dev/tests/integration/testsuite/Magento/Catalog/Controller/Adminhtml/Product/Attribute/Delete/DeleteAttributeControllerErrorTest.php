<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Attribute\Delete;

use Magento\Catalog\Model\Category;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;

/**
 * Error during delete attribute using catalog/product_attribute/delete controller action.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class DeleteAttributeControllerErrorTest extends AbstractDeleteAttributeControllerTest
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->escaper = $this->_objectManager->get(Escaper::class);
        $this->attributeRepository = $this->_objectManager->get(AttributeRepositoryInterface::class);
    }

    /**
     * Try to delete attribute via controller action without attribute ID.
     *
     * @return void
     */
    public function testDispatchWithoutAttributeId(): void
    {
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->dispatch(sprintf($this->uri, ''));
        $this->assertSessionMessages(
            $this->equalTo([$this->escaper->escapeHtml((string)__('We can\'t find an attribute to delete.'))]),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Try to delete category attribute via controller action.
     *
     * @magentoDataFixture Magento/Catalog/_files/category_attribute.php
     *
     * @return void
     */
    public function testDispatchWithNonProductAttribute(): void
    {
        $categoryAttribute = $this->attributeRepository->get(
            Category::ENTITY,
            'test_attribute_code_666'
        );
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->dispatch(sprintf($this->uri, $categoryAttribute->getAttributeId()));
        $this->assertSessionMessages(
            $this->equalTo([$this->escaper->escapeHtml((string)__('We can\'t delete the attribute.'))]),
            MessageInterface::TYPE_ERROR
        );
    }
}
