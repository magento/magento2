<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml;

use Magento\Framework\Filesystem\DirectoryList;

/**
 * @magentoAppArea adminhtml
 */
class ImportTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    public function testGetFilterAction()
    {
        $this->dispatch('backend/admin/import/index');
        $body = $this->getResponse()->getBody();
        $this->assertContains(
            (string)\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\ImportExport\Helper\Data::class
            )->getMaxUploadSizeMessage(),
            $body
        );
    }
}
