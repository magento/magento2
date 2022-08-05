<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block;

use Magento\Customer\Block\SectionConfig;
use Magento\Framework\Config\DataInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SectionConfigTest extends TestCase
{
    /** @var \Magento\Customer\Block\block */
    protected $block;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Context|MockObject */
    protected $context;

    /** @var DataInterface|MockObject */
    protected $sectionConfig;

    /** @var EncoderInterface|MockObject */
    protected $encoder;

    protected function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->sectionConfig = $this->getMockForAbstractClass(DataInterface::class);
        $this->encoder = $this->getMockForAbstractClass(EncoderInterface::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->block = $this->objectManagerHelper->getObject(
            SectionConfig::class,
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
