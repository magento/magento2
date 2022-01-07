<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Widget\Model\ResourceModel\Widget\Instance\Collection;
use Magento\Widget\Model\ResourceModel\Widget\Instance\CollectionFactory;

/**
 * Test for delete widget controller
 *
 * @see \Magento\Widget\Controller\Adminhtml\Widget\Instance\Delete
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class DeleteTest extends AbstractBackendController
{
    /** @var Collection */
    private $widgetCollection;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->widgetCollection = $this->_objectManager->get(CollectionFactory::class)->create();
    }

    /**
     * @magentoDataFixture Magento/Widget/_files/new_widget.php
     *
     * @return void
     */
    public function testDeleteWidget(): void
    {
        $widget = $this->widgetCollection->addFieldToFilter('title', 'New Sample widget title')->getFirstItem();
        $this->assertNotNull($widget->getInstanceId());
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->getRequest()->setParams(['instance_id' => $widget->getInstanceId()]);
        $this->dispatch('backend/admin/widget_instance/delete');
        $this->assertSessionMessages(
            $this->containsEqual((string)__('The widget instance has been deleted.')),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('admin/widget_instance/index'));
    }
}
