<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Test\Unit\Model\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\Config\Source\ResetMethod;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Security\Model\Config\Source\ResetMethod testing
 */
class ResetMethodTest extends TestCase
{
    /**
     * @var ResetMethod
     */
    protected $model;

    /**
     * @var  ObjectManager
     */
    protected $objectManager;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject(ResetMethod::class);
    }

    public function testToOptionArray()
    {
        $expected = [
            [
                'value' => ResetMethod::OPTION_BY_IP_AND_EMAIL,
                'label' => __('By IP and Email')
            ],
            [
                'value' => ResetMethod::OPTION_BY_IP,
                'label' => __('By IP')
            ],
            [
                'value' => ResetMethod::OPTION_BY_EMAIL,
                'label' => __('By Email')
            ],
            [
                'value' => ResetMethod::OPTION_NONE,
                'label' => __('None')
            ],
        ];
        $this->assertEquals($expected, $this->model->toOptionArray());
    }

    public function testToArray()
    {
        $expected = [
            ResetMethod::OPTION_BY_IP_AND_EMAIL => __('By IP and Email'),
            ResetMethod::OPTION_BY_IP => __('By IP'),
            ResetMethod::OPTION_BY_EMAIL => __('By Email'),
            ResetMethod::OPTION_NONE => __('None'),
        ];
        $this->assertEquals($expected, $this->model->toArray());
    }
}
