<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Authorization\Policy;

class DefaultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Authorization\Policy\DefaultPolicy
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Authorization\Policy\DefaultPolicy();
    }

    public function testIsAllowedReturnsTrueForAnyResource()
    {
        $this->assertTrue($this->_model->isAllowed('any_role', 'any_resource'));
    }
}
