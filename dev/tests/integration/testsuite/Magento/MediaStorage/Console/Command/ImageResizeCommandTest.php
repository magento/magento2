<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\MediaStorage\Console\Command;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Integration testing for ImageResizeCommand class
 */
class ImageResizeCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\MediaStorage\Console\Command\ImagesResizeCommand
     */
    private $imageResizeCommand;

    /**
     * @var ArgvInput
     */
    private $input;

    /**
     * @var ConsoleOutput
     */
    private $output;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->imageResizeCommand = $this->objectManager->create(
            \Magento\MediaStorage\Console\Command\ImagesResizeCommand::class
        );

        $this->input = $this->objectManager->create(ArgvInput::class, ['argv' => ['catalog:image:resize']]);
        $this->output = $this->objectManager->create(ConsoleOutput::class);
    }

    /**
     * Test that catalog:image:resize command executed successfully with missing image file
     *
     * @magentoDataFixture Magento/MediaStorage/_files/product_with_missed_image.php
     */
    public function testRunResizeWithMissingFile()
    {
        $resultCode = $this->imageResizeCommand->run($this->input, $this->output);
        $this->assertSame($resultCode, 0);
    }
}
