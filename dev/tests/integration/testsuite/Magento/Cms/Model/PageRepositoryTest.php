<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Model;

use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test page repo.
 */
class PageRepositoryTest extends TestCase
{
    /**
     * @var PageRepositoryInterface
     */
    private $repo;

    /**
     * @var GetPageByIdentifierInterface
     */
    private $retriever;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->repo = $objectManager->get(PageRepositoryInterface::class);
        $this->retriever = $objectManager->get(GetPageByIdentifierInterface::class);
    }

    /**
     * Test that the field is deprecated.
     *
     * @throws \Throwable
     * @magentoDataFixture Magento/Cms/_files/pages_with_layout_xml.php
     * @return void
     */
    public function testSaveUpdateXml(): void
    {
        $page = $this->retriever->execute('test_custom_layout_page_1', 0);
        $page->setTitle($page->getTitle() . 'TEST');

        //Is successfully saved without changes to the custom layout xml.
        $page = $this->repo->save($page);

        //New value is not accepted.
        $page->setCustomLayoutUpdateXml('<container name="new_container_for_save_update_xml" />');
        $forbidden = false;
        try {
            $page = $this->repo->save($page);
        } catch (CouldNotSaveException $exception) {
            $forbidden = true;
        }
        $this->assertTrue($forbidden);

        //New value is not accepted.
        $page->setLayoutUpdateXml('<container name="new_container_for_save_update_xml2" />');
        $forbidden = false;
        try {
            $page = $this->repo->save($page);
        } catch (CouldNotSaveException $exception) {
            $forbidden = true;
        }
        $this->assertTrue($forbidden);

        //Can be removed
        $page->setCustomLayoutUpdateXml(null);
        $page->setLayoutUpdateXml(null);
        $page = $this->repo->save($page);
        $this->assertEmpty($page->getCustomLayoutUpdateXml());
        $this->assertEmpty($page->getLayoutUpdateXml());
    }

    /**
     * Verifies that cms page with identifier which duplicates existed route shouldn't be saved
     *
     * @return void
     * @throws \Throwable
     * @magentoDataFixture Magento/Cms/_files/pages.php
     */
    public function testSaveWithRouteDuplicate(): void
    {
        $page = $this->retriever->execute('page100', 0);
        $page->setIdentifier('customer');
        $this->expectException(CouldNotSaveException::class);
        $this->repo->save($page);
    }
}
