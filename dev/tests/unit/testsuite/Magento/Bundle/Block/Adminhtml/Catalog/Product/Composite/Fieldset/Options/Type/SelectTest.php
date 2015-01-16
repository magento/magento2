<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type;

use Magento\TestFramework\Helper\ObjectManager;

class SelectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Select
     */
    protected $block;

    public function setUp()
    {
        $this->block = (new ObjectManager($this))
            ->getObject('Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Select');
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
