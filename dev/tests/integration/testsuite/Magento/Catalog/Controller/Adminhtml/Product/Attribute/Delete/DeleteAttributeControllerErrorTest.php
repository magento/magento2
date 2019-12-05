<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Attribute\Delete;

use Magento\Customer\Api\AddressMetadataManagementInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Error during delete attribute using catalog/product_attribute/delete controller action.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class DeleteAttributeControllerErrorTest extends AbstractBackendController
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
        $this->dispatch(sprintf(AbstractDeleteAttributeControllerTest::DELETE_ATTRIBUTE_URL, ''));
        $this->assertSessionMessages(
            $this->equalTo([$this->escaper->escapeHtml((string)__('We can\'t find an attribute to delete.'))]),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Try to delete customer attribute via controller action.
     *
     * @magentoDataFixture Magento/Customer/_files/attribute_user_defined_address.php
     *
     * @return void
     */
    public function testDispatchWithCustomerAttributeId(): void
    {
        $customerAttribute = $this->attributeRepository->get(
            AddressMetadataManagementInterface::ENTITY_TYPE_ADDRESS,
            'address_user_attribute'
        );
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->dispatch(
            sprintf(
                AbstractDeleteAttributeControllerTest::DELETE_ATTRIBUTE_URL,
                $customerAttribute->getAttributeId()
            )
        );
        $this->assertSessionMessages(
            $this->equalTo([$this->escaper->escapeHtml((string)__('We can\'t delete the attribute.'))]),
            MessageInterface::TYPE_ERROR
        );
    }
}
