<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type;

use Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type\Radio;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\Framework\DataObject;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class RadioTest extends TestCase
{
    /**
     * @var Radio
     */
    protected $block;

    protected function setUp(): void
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
            ->getObject(Radio::class, ['htmlRenderer' => $secureRendererMock]);
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
