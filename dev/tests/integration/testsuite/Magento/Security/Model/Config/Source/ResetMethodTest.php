<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\Config\Source;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class \Magento\Security\Model\Config\Source\ResetMethod
 */
class ResetMethodTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Security\Model\Config\Source\ResetMethod
     */
    protected $resetMethod;

    /**
     * Set up
     */
    protected function setUp()
    {
        parent::setUp();

        $this->resetMethod = Bootstrap::getObjectManager()->get('Magento\Security\Model\Config\Source\ResetMethod');
    }

    /**
     * Tear down
     */
    protected function tearDown()
    {
        $this->resetMethod = null;
        parent::tearDown();
    }


    /**
     * Options getter test
     */
    public function testToOptionArray()
    {
        $this->assertEquals(
            [
                [
                    'value' => \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_IP_AND_EMAIL,
                    'label' => __('By IP and Email')],
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
            ],
            $this->resetMethod->toOptionArray()
        );
    }

    /**
     * Options getter in "key-value" format test
     */
    public function testToArray()
    {
        $this->assertEquals(
            [
                \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_IP_AND_EMAIL => __('By IP and Email'),
                \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_IP => __('By IP'),
                \Magento\Security\Model\Config\Source\ResetMethod::OPTION_BY_EMAIL => __('By Email'),
                \Magento\Security\Model\Config\Source\ResetMethod::OPTION_NONE => __('None')
            ],
            $this->resetMethod->toArray()
        );
    }
}
