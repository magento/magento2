<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Test\Unit\Model\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Test class for \Magento\Security\Model\Config\Source\ResetMethod testing
 */
class ResetMethodTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Security\Model\Config\Source\ResetMethod
     */
    protected $model;

    /**
     * @var  \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->model = $this->objectManager->getObject('\Magento\Security\Model\Config\Source\ResetMethod');
    }

    public function testToOptionArray()
    {
        $expected = [
            [
                'value' => \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_IP_AND_EMAIL,
                'label' => __('By IP and Email')
            ],
            [
                'value' => \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_IP,
                'label' => __('By IP')
            ],
            [
                'value' => \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_EMAIL,
                'label' => __('By Email')
            ],
            [
                'value' => \Magento\Security\Model\Config\Source\ResetMethod::OPTION_NONE,
                'label' => __('None')
            ],
        ];
        $this->assertEquals($expected, $this->model->toOptionArray());
    }

    public function testToArray()
    {
        $expected = [
            \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_IP_AND_EMAIL => __('By IP and Email'),
            \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_IP => __('By IP'),
            \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_EMAIL => __('By Email'),
            \Magento\Security\Model\Config\Source\ResetMethod::OPTION_NONE => __('None'),
        ];
        $this->assertEquals($expected, $this->model->toArray());
    }
}
