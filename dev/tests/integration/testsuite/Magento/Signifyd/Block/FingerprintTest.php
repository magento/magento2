<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Block;

use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;

class FingerprintTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $bootstrap = Bootstrap::getInstance();
        $bootstrap->loadArea(Area::AREA_FRONTEND);

        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * Checks if session id attribute is present when the module is enabled.
     *
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 1
     */
    public function testSessionIdPresent()
    {
        self::assertStringContainsString('data-order-session-id',$this->getBlockContents());
    }

    /**
     * Checks if block is an empty when the module is disabled.
     *
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 0
     */
    public function testBlockEmpty()
    {
        self::assertEmpty($this->getBlockContents());
    }

    /**
     * Renders block contents.
     *
     * @return string
     */
    private function getBlockContents()
    {
        $block = $this->objectManager->get(LayoutInterface::class)
            ->createBlock(Fingerprint::class);

        return $block->fetchView($block->getTemplateFile());
    }
}
