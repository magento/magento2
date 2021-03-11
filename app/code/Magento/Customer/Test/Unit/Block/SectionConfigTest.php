<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Block;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class SectionConfigTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Customer\Block\block */
    protected $block;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit\Framework\MockObject\MockObject */
    protected $context;

    /** @var \Magento\Framework\Config\DataInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $sectionConfig;

    /** @var \Magento\Framework\Json\EncoderInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $encoder;

    protected function setUp(): void
    {
        $this->context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->sectionConfig = $this->createMock(\Magento\Framework\Config\DataInterface::class);
        $this->encoder = $this->createMock(\Magento\Framework\Json\EncoderInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            \Magento\Customer\Block\SectionConfig::class,
            [
                'context' => $this->context,
                'sectionConfig' => $this->sectionConfig
            ]
        );
    }

    public function testGetSections()
    {
        $this->sectionConfig->expects($this->once())->method('get')->with('sections')->willReturn(['data']);

        $this->assertEquals(['data'], $this->block->getSections());
    }
}
