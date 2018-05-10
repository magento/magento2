<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;

class ImagesResizeCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CommandTester
     */
    private $tester;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->tester = new CommandTester($this->objectManager->create(ImagesResizeCommand::class));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_with_image.php
     */
    public function testRisizeSuccessfully()
    {
        $returnStatus = $this->tester->execute([]);
        $returnData = $this->tester->getDisplay();
        self::assertContains('Product images resized successfully', $returnData);
        self::assertEquals(0, $returnStatus);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testNoProductImages()
    {
        $returnStatus = $this->tester->execute([]);
        $returnData = $this->tester->getDisplay();
        self::assertContains('No product images to resize', $returnData);
        self::assertEquals(0, $returnStatus);
    }
}
