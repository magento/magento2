<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Controller\Adminhtml\Block;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Checks that cms block can be successfully deleted
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class DeleteTest extends AbstractBackendController
{
    /** @var GetBlockByIdentifierInterface */
    private $getBlockByIdentifier;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var CollectionFactory */
    private $collectionFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->getBlockByIdentifier = $this->_objectManager->get(GetBlockByIdentifierInterface::class);
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
        $this->collectionFactory = $this->_objectManager->get(CollectionFactory::class);
    }

    /**
     * @magentoDataFixture Magento/Cms/_files/block_default_store.php
     *
     * @return void
     */
    public function testDeleteBlock(): void
    {
        $defaultStoreId = (int)$this->storeManager->getStore('default')->getId();
        $blockId = $this->getBlockByIdentifier->execute('default_store_block', $defaultStoreId)->getId();
        $this->getRequest()->setMethod(Http::METHOD_POST)
            ->setParams(['block_id' => $blockId]);
        $this->dispatch('backend/cms/block/delete');
        $this->assertSessionMessages(
            $this->containsEqual((string)__('You deleted the block.')),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('cms/block/index'));
        $collection = $this->collectionFactory->getReport('cms_block_listing_data_source');
        $this->assertNull($collection->getItemByColumnValue(BlockInterface::IDENTIFIER, 'default_store_block'));
    }
}
