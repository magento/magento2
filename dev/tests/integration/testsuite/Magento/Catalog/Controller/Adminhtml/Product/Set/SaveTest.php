<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Set;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Developer\Model\Logger\Handler\Syslog;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Logger\Handler\System;
use Magento\Framework\Logger\Monolog;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Psr\Log\LoggerInterface;

/**
 * Testing for saving an existing or creating a new attribute set.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 */
class SaveTest extends AbstractBackendController
{
    /**
     * @var string
     */
    private $systemLogPath = '';

    /**
     * @var Monolog
     */
    private $logger;

    /**
     * @var Syslog
     */
    private $syslogHandler;

    /**
     * @var AttributeManagementInterface
     */
    private $attributeManagement;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Repository
     */
    private $attributeRepository;

    /**
     * @var AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var Json
     */
    private $json;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->logger = $this->_objectManager->get(LoggerInterface::class);
        $this->syslogHandler = $this->_objectManager->create(
            Syslog::class,
            [
                'filePath' => Bootstrap::getInstance()->getAppTempDir(),
            ]
        );
        $this->attributeManagement = $this->_objectManager->get(AttributeManagementInterface::class);
        $this->productRepository = $this->_objectManager->get(ProductRepositoryInterface::class);
        $this->attributeRepository = $this->_objectManager->get(Repository::class);
        $this->dataObjectHelper = $this->_objectManager->get(DataObjectHelper::class);
        $this->attributeSetRepository = $this->_objectManager->get(AttributeSetRepositoryInterface::class);
        $this->eavConfig = $this->_objectManager->get(Config::class);
        $this->json = $this->_objectManager->get(Json::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->attributeRepository->get('country_of_manufacture')->setIsUserDefined(false);
        parent::tearDown();
    }

    /**
     * Test that new attribute set based on default attribute set will be successfully created.
     *
     * @magentoDbIsolation enabled
     *
     * @return void
     */
    public function testCreateNewAttributeSetBasedOnDefaultAttributeSet(): void
    {
        $this->createAttributeSetBySkeletonAndAssert(
            'Attribute set name for test',
            $this->getCatalogProductDefaultAttributeSetId()
        );
    }

    /**
     * Test that new attribute set based on custom attribute set will be successfully created.
     *
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_with_renamed_group.php
     *
     * @magentoDbIsolation enabled
     *
     * @return void
     */
    public function testCreateNewAttributeSetBasedOnCustomAttributeSet(): void
    {
        $existCustomAttributeSet = $this->getAttributeSetByName('attribute_set_test');
        $this->createAttributeSetBySkeletonAndAssert(
            'Attribute set name for test',
            (int)$existCustomAttributeSet->getAttributeSetId()
        );
    }

    /**
     * Test that new attribute set based on custom attribute set will be successfully created.
     *
     * @magentoDbIsolation enabled
     *
     * @return void
     */
    public function testGotErrorDuringCreateAttributeSetWithoutName(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            [
                'gotoEdit' => '1',
                'skeleton_set' => $this->getCatalogProductDefaultAttributeSetId(),
                'attribute_set_name' => ''
            ]
        );
        $this->dispatch('backend/catalog/product_set/save/');
        $this->assertSessionMessages(
            $this->equalTo([(string)__('The attribute set name is empty. Enter the name and try again.')]),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test that exception throws during save attribute set name process if name of attribute set already exists.
     *
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_with_renamed_group.php
     * @return void
     */
    public function testAlreadyExistsExceptionProcessingWhenGroupCodeIsDuplicated(): void
    {
        $attributeSet = $this->getAttributeSetByName('attribute_set_test');
        $this->assertNotEmpty($attributeSet, 'Attribute set with name "attribute_set_test" is missed');

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            'data',
            $this->json->serialize(
                [
                    'attribute_set_name' => 'attribute_set_test',
                    'groups' => [
                        ['ynode-418', 'attribute-group-name', 1],
                    ],
                    'attributes' => [
                        ['9999', 'ynode-418', 1, null]
                    ],
                    'not_attributes' => [],
                    'removeGroups' => [],
                ]
            )
        );
        $this->dispatch('backend/catalog/product_set/save/id/' . $attributeSet->getAttributeSetId());

        $jsonResponse = $this->json->unserialize($this->getResponse()->getBody());
        $this->assertNotNull($jsonResponse);
        $this->assertEquals(1, $jsonResponse['error']);
        $this->assertStringContainsString(
            (string)__(
                'Attribute group with same code already exist.'
                . ' Please rename &quot;attribute-group-name&quot; group'
            ),
            $jsonResponse['message']
        );
    }

    /**
     * Test behavior when attribute set was changed to a new set
     * with deleted attribute from the previous set.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_based_on_default.php
     * @magentoDbIsolation disabled
     * @return void
     */
    public function testRemoveAttributeFromAttributeSet(): void
    {
        $message = 'Attempt to load value of nonexistent EAV attribute';
        $this->removeSyslog();
        $attributeSet = $this->getAttributeSetByName('new_attribute_set');
        $product = $this->productRepository->get('simple');
        $this->attributeRepository->get('country_of_manufacture')->setIsUserDefined(true);
        $this->attributeManagement->unassign($attributeSet->getId(), 'country_of_manufacture');
        $productData = [
            'country_of_manufacture' => 'Angola'
        ];
        $this->dataObjectHelper->populateWithArray($product, $productData, ProductInterface::class);
        $this->productRepository->save($product);
        $product->setAttributeSetId($attributeSet->getId());
        $product = $this->productRepository->save($product);
        $this->dispatch('backend/catalog/product/edit/id/' . $product->getEntityId());
        $syslogPath = $this->getSyslogPath();
        $syslogContent = file_exists($syslogPath) ? file_get_contents($syslogPath) : '';
        $this->assertStringNotContainsString($message, $syslogContent);
    }

    /**
     * Retrieve system.log file path.
     *
     * @return string
     */
    private function getSyslogPath(): string
    {
        if (!$this->systemLogPath) {
            foreach ($this->logger->getHandlers() as $handler) {
                if ($handler instanceof System) {
                    $this->systemLogPath = $handler->getUrl();
                }
            }
        }

        return $this->systemLogPath;
    }

    /**
     * Remove system.log file
     *
     * @return void
     */
    private function removeSyslog(): void
    {
        $this->syslogHandler->close();
        if (file_exists($this->getSyslogPath())) {
            unlink($this->getSyslogPath());
        }
    }

    /**
     * Search and return attribute set by name.
     *
     * @param string $attributeSetName
     * @return AttributeSetInterface|null
     */
    private function getAttributeSetByName(string $attributeSetName): ?AttributeSetInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->addFilter('attribute_set_name', $attributeSetName);
        $result = $this->attributeSetRepository->getList($searchCriteriaBuilder->create());

        $items = $result->getItems();

        return array_pop($items);
    }

    /**
     * Create attribute set by skeleton attribute set id and assert that attribute set
     * created successfully and attributes from skeleton attribute set and created attribute set are equals.
     *
     * @param string $attributeSetName
     * @param int $skeletonAttributeSetId
     * @return void
     */
    private function createAttributeSetBySkeletonAndAssert(
        string $attributeSetName,
        int $skeletonAttributeSetId
    ): void {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            [
                'attribute_set_name' => $attributeSetName,
                'gotoEdit' => '1',
                'skeleton_set' => $skeletonAttributeSetId,
            ]
        );
        $this->dispatch('backend/catalog/product_set/save/');
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You saved the attribute set.')]),
            MessageInterface::TYPE_SUCCESS
        );
        $createdAttributeSet = $this->getAttributeSetByName($attributeSetName);
        $existAttributeSet = $this->attributeSetRepository->get($skeletonAttributeSetId);

        $this->assertNotNull($createdAttributeSet);
        $this->assertEquals($attributeSetName, $createdAttributeSet->getAttributeSetName());

        $this->assertAttributeSetsAttributesAreEquals($createdAttributeSet, $existAttributeSet);
    }

    /**
     * Assert that both attribute sets contains identical attributes by attribute ids.
     *
     * @param AttributeSetInterface $createdAttributeSet
     * @param AttributeSetInterface $existAttributeSet
     * @return void
     */
    private function assertAttributeSetsAttributesAreEquals(
        AttributeSetInterface $createdAttributeSet,
        AttributeSetInterface $existAttributeSet
    ): void {
        $expectedAttributeIds = array_keys(
            $this->attributeManagement->getAttributes(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $existAttributeSet->getAttributeSetId()
            )
        );
        sort($expectedAttributeIds);
        $actualAttributeIds = array_keys(
            $this->attributeManagement->getAttributes(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $createdAttributeSet->getAttributeSetId()
            )
        );
        sort($actualAttributeIds);
        $this->assertSame($expectedAttributeIds, $actualAttributeIds);
    }

    /**
     * Retrieve default catalog product attribute set ID.
     *
     * @return int
     */
    private function getCatalogProductDefaultAttributeSetId(): int
    {
        return (int)$this->eavConfig
            ->getEntityType(ProductAttributeInterface::ENTITY_TYPE_CODE)
            ->getDefaultAttributeSetId();
    }
}
