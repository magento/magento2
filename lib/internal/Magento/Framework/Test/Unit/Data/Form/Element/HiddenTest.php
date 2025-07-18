<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\Data\Form\Element;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Hidden;
use Magento\Framework\Escaper;
use Magento\Framework\Math\Random;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Framework\Data\Form\Element\Hidden.
 */
class HiddenTest extends TestCase
{
    /**
     * @var Hidden
     */
    private $element;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $objects = [
            [
                SecureHtmlRenderer::class,
                $this->createMock(SecureHtmlRenderer::class)
            ],
            [
                Random::class,
                $this->createMock(Random::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $escaper = $objectManager->getObject(
            Escaper::class
        );
        $this->element = $objectManager->getObject(
            Hidden::class,
            [
                'escaper' => $escaper
            ]
        );
    }

    /**
     * @param mixed $value
     *
     * @dataProvider getElementHtmlDataProvider
     */
    public function testGetElementHtml($value)
    {
        $form = $this->createMock(Form::class);
        $this->element->setForm($form);
        $this->element->setValue($value);
        $html = $this->element->getElementHtml();

        if (is_array($value)) {
            foreach ($value as $item) {
                $this->assertStringContainsString($item, $html);
            }
            return;
        }
        $this->assertStringContainsString($value, $html);
    }

    /**
     * @return array
     */
    public static function getElementHtmlDataProvider()
    {
        return [
            ['some_value'],
            ['value' => ['1', '2']],
        ];
    }
}
