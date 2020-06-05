<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type;

use Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Checkbox;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class CheckboxTest extends TestCase
{
    /**
     * @var Checkbox
     */
    protected $block;

    protected function setUp(): void
    {
        $this->block = (new ObjectManager($this))
            ->getObject(
                Checkbox::class
            );
    }

    public function testSetValidationContainer()
    {
        $elementId = 'element-id';
        $containerId = 'container-id';

        $result = $this->block->setValidationContainer($elementId, $containerId);

        $this->assertStringContainsString($elementId, $result);
        $this->assertStringContainsString($containerId, $result);
    }
}
