<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Set;

use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Request\Http as HttpRequest;

class SaveTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_with_renamed_group.php
     */
    public function testAlreadyExistsExceptionProcessingWhenGroupCodeIsDuplicated()
    {
        $attributeSet = $this->getAttributeSetByName('attribute_set_test');
        $this->assertNotEmpty($attributeSet, 'Attribute set with name "attribute_set_test" is missed');

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue('data', json_encode([
            'attribute_set_name' => 'attribute_set_test',
            'groups' => [
                ['ynode-418', 'attribute-group-name', 1],
            ],
            'attributes' => [
                ['9999', 'ynode-418', 1, null]
            ],
            'not_attributes' => [],
            'removeGroups' => [],
        ]));
        $this->dispatch('backend/catalog/product_set/save/id/' . $attributeSet->getAttributeSetId());

        $jsonResponse = json_decode($this->getResponse()->getBody());
        $this->assertNotNull($jsonResponse);
        $this->assertEquals(1, $jsonResponse->error);
        $this->assertContains(
            'Attribute group with same code already exist. Please rename &quot;attribute-group-name&quot; group',
            $jsonResponse->message
        );
    }

    /**
     * @param string $attributeSetName
     * @return AttributeSetInterface|null
     */
    protected function getAttributeSetByName($attributeSetName)
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->addFilter('attribute_set_name', $attributeSetName);

        /** @var AttributeSetRepositoryInterface $attributeSetRepository */
        $attributeSetRepository = $objectManager->get(AttributeSetRepositoryInterface::class);
        $result = $attributeSetRepository->getList($searchCriteriaBuilder->create());

        $items = $result->getItems();
        return $result->getTotalCount() ? array_pop($items) : null;
    }
}
