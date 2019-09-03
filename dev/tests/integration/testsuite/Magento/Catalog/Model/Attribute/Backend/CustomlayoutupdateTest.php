<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Category;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;

/**
 * Test 'custom layout' attribute.
 */
class CustomlayoutupdateTest extends TestCase
{
    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var Customlayoutupdate
     */
    private $attribute;

    /**
     * @var Category
     */
    private $category;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->categoryFactory = Bootstrap::getObjectManager()->get(CategoryFactory::class);
        $this->category = $this->categoryFactory->create();
        $this->category->load(2);
        $this->attribute = $this->category->getAttributes()['custom_layout_update']->getBackend();
    }

    /**
     * Test that attribute cannot be modified but only removed completely.
     *
     * @return void
     * @throws \Throwable
     */
    public function testImmutable(): void
    {
        //Value is empty
        $this->category->setCustomAttribute('custom_layout_update', null);
        $this->category->setOrigData('custom_layout_update', null);
        $this->attribute->beforeSave($this->category);

        //New value
        $this->category->setCustomAttribute('custom_layout_update', 'test');
        $this->category->setOrigData('custom_layout_update', null);
        $caughtException = false;
        try {
            $this->attribute->beforeSave($this->category);
        } catch (LocalizedException $exception) {
            $caughtException = true;
        }
        $this->assertTrue($caughtException);
        $this->category->setCustomAttribute('custom_layout_update', 'testNew');
        $this->category->setOrigData('custom_layout_update', 'test');
        $caughtException = false;
        try {
            $this->attribute->beforeSave($this->category);
        } catch (LocalizedException $exception) {
            $caughtException = true;
        }
        $this->assertTrue($caughtException);

        //Removing a value
        $this->category->setCustomAttribute('custom_layout_update', null);
        $this->category->setOrigData('custom_layout_update', 'test');
        $this->attribute->beforeSave($this->category);
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
        $this->category->setCustomAttribute('custom_layout_update', 'test');
        $this->category->setOrigData('custom_layout_update', 'test');
        $this->category->setCustomAttribute('custom_layout_update_file', 'new');
        $this->attribute->beforeSave($this->category);
        $this->assertEmpty($this->category->getCustomAttribute('custom_layout_update')->getValue());
        $this->assertEquals('new', $this->category->getCustomAttribute('custom_layout_update_file')->getValue());

        //Existing update chosen
        $this->category->setCustomAttribute('custom_layout_update', 'test');
        $this->category->setOrigData('custom_layout_update', 'test');
        $this->category->setCustomAttribute('custom_layout_update_file', '__existing__');
        $this->attribute->beforeSave($this->category);
        $this->assertEquals('test', $this->category->getCustomAttribute('custom_layout_update')->getValue());
        /** @var AbstractBackend $fileAttribute */
        $fileAttribute = $this->category->getAttributes()['custom_layout_update_file']->getBackend();
        $fileAttribute->beforeSave($this->category);
        $this->assertEquals(null, $this->category->getCustomAttribute('custom_layout_update_file')->getValue());
    }
}
