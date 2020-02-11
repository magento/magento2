<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml;

use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\ImportExport\Helper\Data;
use Magento\Framework\Escaper;

/**
 * @magentoAppArea adminhtml
 */
class ImportTest extends AbstractBackendController
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Get Filter Action
     *
     * @return void
     */
    public function testGetFilterAction()
    {
        $this->dispatch('backend/admin/import/index');
        $body = $this->getResponse()->getBody();
        $message = (string) $this->objectManager->get(Data::class)
            ->getMaxUploadSizeMessage();
        $this->assertContains(
            $this->objectManager->get(Escaper::class)->escapeHtml($message),
            $body
        );
    }
}
