<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Category;
use Magento\TestFramework\Catalog\Model\CategoryLayoutUpdateManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;

/**
 * Test 'custom layout file' attribute.
 */
class AbstractLayoutUpdateTest extends TestCase
{
    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var AbstractLayoutUpdate
     */
    private $attribute;

    /**
     * @var Category
     */
    private $category;
    /**
     * @var CategoryLayoutUpdateManager
     */
    private $layoutManager;

    /**
     * Recreate the category model.
     *
     * @return void
     */
    private function recreateCategory(): void
    {
        $this->category = $this->categoryFactory->create();
        $this->category->load(2);
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->categoryFactory = Bootstrap::getObjectManager()->get(CategoryFactory::class);
        $this->recreateCategory();
        $this->attribute = $this->category->getAttributes()['custom_layout_update_file']->getBackend();
        $this->layoutManager = Bootstrap::getObjectManager()->get(CategoryLayoutUpdateManager::class);
    }

    /**
     * Check that custom layout update file's values erase the old attribute's value.
     *
     * @return void
     * @throws \Throwable
     */
    public function testDependsOnNewUpdate(): void
    {
        //New selected file value is set
        $this->layoutManager->setCategoryFakeFiles(2, ['new']);
        $this->category->setCustomAttribute('custom_layout_update', 'test');
        $this->category->setOrigData('custom_layout_update', 'test');
        $this->category->setCustomAttribute('custom_layout_update_file', 'new');
        $this->attribute->beforeSave($this->category);
        $this->assertEmpty($this->category->getCustomAttribute('custom_layout_update')->getValue());
        $this->assertEquals('new', $this->category->getCustomAttribute('custom_layout_update_file')->getValue());
        $this->assertEmpty($this->category->getData('custom_layout_update'));
        $this->assertEquals('new', $this->category->getData('custom_layout_update_file'));

        //Existing update chosen
        $this->recreateCategory();
        $this->category->setData('custom_layout_update', 'test');
        $this->category->setOrigData('custom_layout_update', 'test');
        $this->category->setData(
            'custom_layout_update_file',
            \Magento\Catalog\Model\Category\Attribute\Backend\LayoutUpdate::VALUE_USE_UPDATE_XML
        );
        $this->attribute->beforeSave($this->category);
        $this->assertEquals('test', $this->category->getData('custom_layout_update'));
        /** @var AbstractBackend $fileAttribute */
        $fileAttribute = $this->category->getAttributes()['custom_layout_update_file']->getBackend();
        $fileAttribute->beforeSave($this->category);
        $this->assertNull($this->category->getData('custom_layout_update_file'));

        //Removing custom layout update by explicitly selecting the new file (or an empty file).
        $this->recreateCategory();
        $this->category->setData('custom_layout_update', 'test');
        $this->category->setOrigData('custom_layout_update', 'test');
        $this->category->setData(
            'custom_layout_update_file',
            \Magento\Catalog\Model\Category\Attribute\Backend\LayoutUpdate::VALUE_NO_UPDATE
        );
        $this->attribute->beforeSave($this->category);
        $this->assertEmpty($this->category->getData('custom_layout_update'));

        //Empty value doesn't change the old attribute. Any non-string value can be used to represent an empty value.
        $this->recreateCategory();
        $this->category->setData('custom_layout_update', 'test');
        $this->category->setOrigData('custom_layout_update', 'test');
        $this->category->setData(
            'custom_layout_update_file',
            false
        );
        $this->attribute->beforeSave($this->category);
        $this->assertEquals('test', $this->category->getData('custom_layout_update'));
        $this->assertNull($this->category->getData('custom_layout_update_file'));
    }
}
