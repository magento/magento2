<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Block\Form;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use PHPUnit\Framework\TestCase;

/**
 * Test for remember me checkbox on create customer account page
 *
 * @see \Magento\Persistent\Block\Form\Remember
 * @magentoAppArea frontend
 */
class RememberTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Remember */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Remember::class)
            ->setTemplate('Magento_Persistent::remember_me.phtml');
    }

    /**
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_default 0
     *
     * @return void
     */
    public function testRememberMeEnabled(): void
    {
        $html = $this->block->toHtml();
        $this->assertFalse($this->block->isRememberMeChecked());
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    '//input[@name="persistent_remember_me"]/following-sibling::label/span[contains(text(), "%s")]',
                    __('Remember Me')
                ),
                $html
            ),
            'Remember Me checkbox wasn\'t found.'
        );
    }

    /**
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_default 1
     *
     * @return void
     */
    public function testRememberMeAndRememberDefaultEnabled(): void
    {
        $blockHtml = $this->block->toHtml();
        $this->assertTrue($this->block->isRememberMeChecked());
        $this->assertEquals(
            1,
            Xpath::getElementsCountForXpath(
                sprintf(
                    '//input[@name="persistent_remember_me"]/following-sibling::label/span[contains(text(), "%s")]',
                    __('Remember Me')
                ),
                $blockHtml
            ),
            'Remember Me checkbox wasn\'t found or not checked by default.'
        );
    }

    /**
     * @magentoConfigFixture current_store persistent/options/enabled 0
     *
     * @return void
     */
    public function testPersistentDisabled(): void
    {
        $this->assertEmpty($this->block->toHtml());
    }

    /**
     * @magentoConfigFixture current_store persistent/options/enabled 1
     * @magentoConfigFixture current_store persistent/options/remember_enabled 0
     *
     * @return void
     */
    public function testRememberMeDisabled(): void
    {
        $this->assertEmpty($this->block->toHtml());
    }
}
