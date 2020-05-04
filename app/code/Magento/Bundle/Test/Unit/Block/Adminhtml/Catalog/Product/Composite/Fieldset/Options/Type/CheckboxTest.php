<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class CheckboxTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Checkbox
     */
    protected $block;

    protected function setUp()
    {
        $secureRendererMock = $this->createMock(SecureHtmlRenderer::class);
        $secureRendererMock->method('renderTag')
            ->willReturnCallback(
                function (string $tag, array $attributes, string $content): string {
                    $attributes = new DataObject($attributes);

                    return "<$tag {$attributes->serialize()}>$content</$tag>";
                }
            );

        $this->block = (new ObjectManager($this))
            ->getObject(
                \Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Checkbox::class,
                ['htmlRenderer' => $secureRendererMock]
            );
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
