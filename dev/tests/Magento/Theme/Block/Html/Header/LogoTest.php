<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Block\Html\Header;

use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Helper\Xpath;
use Magento\Theme\ViewModel\Block\Html\Header\LogoSizeResolver;
use PHPUnit\Framework\TestCase;

/**
 * Test logo page header block
 */
class LogoTest extends TestCase
{
    /**
     * @var Logo
     */
    private $block;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $logoSizeResolver = $objectManager->get(LogoSizeResolver::class);
        $this->block = $objectManager->create(LayoutInterface::class)
            ->createBlock(
                Logo::class,
                'logo',
                [
                    'data' => [
                        'logo_size_resolver' => $logoSizeResolver
                    ]
                ]
            );
    }

    /**
     * @magentoAppArea frontend
     * @magentoConfigFixture current_store design/header/logo_width 260
     * @magentoConfigFixture current_store design/header/logo_height 240
     */
    public function testStoreLogoSize()
    {
        $xpath = '//a[@class="logo"]/img';
        $elements = Xpath::getElementsForXpath($xpath, $this->block->toHtml());
        $this->assertGreaterThan(0, $elements->count(), 'Cannot find element \'' . $xpath . '"\' in the HTML');
        $this->assertEquals(260, $elements->item(0)->getAttribute('width'));
        $this->assertEquals(240, $elements->item(0)->getAttribute('height'));
    }
}
