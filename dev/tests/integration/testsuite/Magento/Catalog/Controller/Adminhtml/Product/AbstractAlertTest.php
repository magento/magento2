<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Xpath;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Class contains of base logic for alert controllers tests
 */
abstract class AbstractAlertTest extends AbstractBackendController
{
    /** @var ProductResource */
    private $productResource;

    /** @var StoreManagerInterface */
    private $storeManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->productResource = $this->_objectManager->get(ProductResource::class);
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
    }

    /**
     * Prepare request
     *
     * @param string $productSku
     * @param string $storeCode
     * @param int|null $limit
     * @return void
     */
    protected function prepareRequest(string $productSku, string $storeCode, ?int $limit): void
    {
        $productId = $this->productResource->getIdBySku($productSku);
        $storeId = $this->storeManager->getStore($storeCode)->getId();
        $this->getRequest()->setMethod(HttpRequest::METHOD_GET);
        $this->getRequest()->setParams(['id' => $productId, 'store' => $storeId, 'limit' => $limit]);
    }

    /**
     * Assert alert grid records count related to provided email
     *
     * @param string $email
     * @param int $expectedCount
     * @return void
     */
    protected function assertGridRecords(string $email, int $expectedCount): void
    {
        $content = $this->getResponse()->getContent();
        $this->assertEquals(
            $expectedCount,
            Xpath::getElementsCountForXpath(sprintf($this->getRecordXpathTemplate(), $email), $content)
        );
    }

    /**
     * Get alert grid record xpath template
     *
     * @return string
     */
    abstract protected function getRecordXpathTemplate(): string;
}
