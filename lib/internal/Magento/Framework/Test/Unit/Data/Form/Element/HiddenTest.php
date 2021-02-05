<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Test\Unit\Data\Form\Element;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test for \Magento\Framework\Data\Form\Element\Hidden.
 */
class HiddenTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Hidden
     */
    private $element;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $escaper = $objectManager->getObject(
            \Magento\Framework\Escaper::class
        );
        $this->element = $objectManager->getObject(
            \Magento\Framework\Data\Form\Element\Hidden::class,
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
        $form = $this->createMock(\Magento\Framework\Data\Form::class);
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
    public function getElementHtmlDataProvider()
    {
        return [
            ['some_value'],
            ['store_ids[]' => ['1', '2']],
        ];
    }
}
