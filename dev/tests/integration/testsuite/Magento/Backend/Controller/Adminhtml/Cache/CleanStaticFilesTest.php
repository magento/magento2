<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Controller\Adminhtml\Cache;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;

class CleanStaticFilesTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    protected function setUp(): void
    {
        $this->resource = 'Magento_Backend::cache';
        $this->uri = 'backend/admin/cache/cleanStaticFiles';
        parent::setUp();
    }

    public function testAclHasAccess()
    {
        // setup
        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = Bootstrap::getObjectManager()->get(\Magento\Framework\Filesystem::class);
        $dirStatic = $filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        $subStaticDir = 'subdir';
        $dirStatic->create($subStaticDir);
        $this->assertTrue($dirStatic->isExist($subStaticDir));

        $dirVar= $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $subVarDir = DirectoryList::TMP_MATERIALIZATION_DIR . '/subdir';
        $dirVar->create($subVarDir);
        $this->assertTrue($dirVar->isExist($subVarDir));

        // test
        parent::testAclHasAccess();
        $this->assertSessionMessages(
            $this->containsEqual("The static files cache has been cleaned."),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS,
            \Magento\Framework\Message\ManagerInterface::class
        );
        $this->assertFalse($dirStatic->isExist($subStaticDir));
        $this->assertTrue($dirVar->isExist(DirectoryList::TMP_MATERIALIZATION_DIR));
        $this->assertFalse($dirVar->isExist($subVarDir));
    }
}
