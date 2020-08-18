<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Layout\Element;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Class to test Layout model functionality with exceptions
 */
class LayoutTestWithExceptions extends TestCase
{
    /**
     * @var Layout
     */
    protected $layout;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $layoutFactory = $objectManager->get(LayoutFactory::class);
        $this->layout = $layoutFactory->create();
        $layoutElement = new Element(
            __DIR__ . '/_files/layout_with_exceptions/layout.xml',
            0,
            true
        );

        $this->layout->setXml($layoutElement);
        $objectManager->get(\Magento\Framework\App\Cache\Type\Layout::class)->clean();
    }

    /**
     * Test to Create structure of elements from the loaded XML configuration with exception
     */
    public function testProcessWithExceptionsDeveloperMode()
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Construction problem.');

        $this->layout->generateElements();
    }

    /**
     * Test to Get all blocks marked for output with exceptions
     *
     * @magentoAppIsolation enabled
     */
    public function testProcessWithExceptions()
    {
        Bootstrap::getObjectManager()->get(State::class)
            ->setMode(State::MODE_DEFAULT);

        $this->layout->generateElements();

        $this->layout->addOutputElement('block.with.broken.constructor');
        $this->layout->addOutputElement('block.with.broken.layout');
        $this->layout->addOutputElement('block.with.broken.action');

        $this->assertEmpty($this->layout->getOutput());
    }
}
