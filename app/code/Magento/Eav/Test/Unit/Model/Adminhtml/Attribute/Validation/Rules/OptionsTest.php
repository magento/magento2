<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Adminhtml\Attribute\Validation\Rules;

use Magento\Eav\Model\Adminhtml\Attribute\Validation\Rules\Options;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class OptionsTest
 */
class OptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Options
     */
    protected $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->model = $objectManager->getObject(Options::class);
    }

    public function testToOptionArray()
    {
        $this->assertEquals(
            [
                ['value' => '', 'label' => __('None')],
                ['value' => 'validate-number', 'label' => __('Decimal Number')],
                ['value' => 'validate-digits', 'label' => __('Integer Number')],
                ['value' => 'validate-email', 'label' => __('Email')],
                ['value' => 'validate-url', 'label' => __('URL')],
                ['value' => 'validate-alpha', 'label' => __('Letters')],
                ['value' => 'validate-alphanum', 'label' => __('Letters (a-z, A-Z) or Numbers (0-9)')]
            ],
            $this->model->toOptionArray()
        );
    }
}
