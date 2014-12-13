<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Customer\Model\Config\Source\Address;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Config\Source\Address\Type
     */
    protected $model;

    protected function setUp()
    {
        $this->model = new \Magento\Customer\Model\Config\Source\Address\Type();
    }

    public function testToOptionArray()
    {
        $expected = ['billing' => 'Billing Address','shipping' => 'Shipping Address'];
        $this->assertEquals($expected, $this->model->toOptionArray());
    }
}
