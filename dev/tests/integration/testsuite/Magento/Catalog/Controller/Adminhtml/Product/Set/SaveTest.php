<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Set;

use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Eav\Api\AttributeManagementInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Developer\Model\Logger\Handler\Syslog;
use Magento\Framework\Logger\Monolog;
use Magento\Catalog\Model\Product\Attribute\Repository;

/**
 * Test save attribute set
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends \Magento\TestFramework\TestCase\AbstractBackendController
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
     * @inheritDoc
     */
    public function setUp()
    {
        parent::setUp();
        $this->logger = $this->_objectManager->get(Monolog::class);
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
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function tearDown()
    {
        $this->attributeRepository->get('country_of_manufacture')->setIsUserDefined(false);
        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_with_renamed_group.php
     */
    public function testAlreadyExistsExceptionProcessingWhenGroupCodeIsDuplicated()
    {
        $attributeSet = $this->getAttributeSetByName('attribute_set_test');
        $this->assertNotEmpty($attributeSet, 'Attribute set with name "attribute_set_test" is missed');

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            'data',
            json_encode(
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

    /**
     * Test behavior when attribute set was changed to a new set
     * with deleted attribute from the previous set
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_based_on_default.php
     * @magentoDbIsolation disabled
     */
    public function testRemoveAttributeFromAttributeSet()
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
        $this->assertNotContains($message, $syslogContent);
    }

    /**
     * Retrieve system.log file path
     *
     * @return string
     */
    private function getSyslogPath(): string
    {
        if (!$this->systemLogPath) {
            foreach ($this->logger->getHandlers() as $handler) {
                if ($handler instanceof \Magento\Framework\Logger\Handler\System) {
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
    private function removeSyslog()
    {
        $this->syslogHandler->close();
        if (file_exists($this->getSyslogPath())) {
            unlink($this->getSyslogPath());
        }
    }
}
