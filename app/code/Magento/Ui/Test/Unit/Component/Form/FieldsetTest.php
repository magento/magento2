<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Test\Unit\Component\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Form\Fieldset;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class FieldTest
 *
 * Test for class \Magento\Ui\Component\Form\Fieldset
 */
class FieldsetTest extends TestCase
{
    const NAME = 'fieldset';

    /**
     * @var Fieldset
     */
    protected $fieldset;

    /**
     * @var ContextInterface|MockObject
     */
    private $context;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(ContextInterface::class)
            ->getMockForAbstractClass();

        $this->fieldset = new Fieldset(
            $this->context,
            [],
            []
        );
    }

    /**
     * Run test for getComponentName() method
     *
     * @return void
     *
     */
    public function testGetComponentName()
    {
        $this->assertEquals(self::NAME, $this->fieldset->getComponentName());
    }
}
