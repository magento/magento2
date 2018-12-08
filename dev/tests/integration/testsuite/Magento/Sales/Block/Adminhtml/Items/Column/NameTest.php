<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Items\Column;

/**
 * @magentoAppArea adminhtml
 */
class NameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Name
     */
    private $block;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $layout \Magento\Framework\View\Layout */
        $layout = $objectManager->create(\Magento\Framework\View\LayoutInterface::class);
        /** @var $block \Magento\Sales\Block\Adminhtml\Items\AbstractItems */
        $this->block = $layout->createBlock(Name::class, 'block');
    }

<<<<<<< HEAD
    /**
     * @return void
     */
    public function testTruncateString()
=======
    public function testTruncateString() : void
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        $remainder = '';
        $this->assertEquals(
            '12345',
            $this->block->truncateString('1234567890', 5, '', $remainder)
        );
    }

<<<<<<< HEAD
    /**
     * @return void
     */
    public function testGetFormattedOption()
=======
    public function testGetFormattedOptiong() : void
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        $this->assertEquals(
            [
                'value' => '1234567890123456789012345678901234567890123456789012345',
                'remainder' => '67890',
            ],
            $this->block->getFormattedOption(
                '123456789012345678901234567890123456789012345678901234567890'
            )
        );
    }
}
