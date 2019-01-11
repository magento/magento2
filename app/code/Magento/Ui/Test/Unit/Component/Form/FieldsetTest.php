<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Form;

use Magento\Ui\Component\Form\Fieldset;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class FieldTest
 *
 * Test for class \Magento\Ui\Component\Form\Fieldset
 */
class FieldsetTest extends \PHPUnit\Framework\TestCase
{
    const NAME = 'fieldset';

    /**
     * @var Fieldset
     */
    protected $fieldset;

    /**
     * @var ContextInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponent\ContextInterface::class)
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
