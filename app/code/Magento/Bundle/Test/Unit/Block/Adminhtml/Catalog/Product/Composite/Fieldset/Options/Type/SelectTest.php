<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SelectTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Select
     */
    protected $block;

    protected function setUp()
    {
        $this->block = (new ObjectManager($this))
            ->getObject(\Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Select::class);
    }

    public function testSetValidationContainer()
    {
        $elementId = 'element-id';
        $containerId = 'container-id';

        $result = $this->block->setValidationContainer($elementId, $containerId);

        $this->assertContains($elementId, $result);
        $this->assertContains($containerId, $result);
    }
}
