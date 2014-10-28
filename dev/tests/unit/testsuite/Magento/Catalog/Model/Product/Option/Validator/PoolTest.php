<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Model\Product\Option\Validator;

class PoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Option\Validator\Pool
     */
    protected $pool;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $defaultValidatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectValidatorMock;

    protected function setUp()
    {
        $this->defaultValidatorMock = $this->getMock(
            'Magento\Catalog\Model\Product\Option\Validator\DefaultValidator', [], [], '', false
        );
        $this->selectValidatorMock = $this->getMock(
            'Magento\Catalog\Model\Product\Option\Validator\Select', [], [], '', false
        );
        $this->pool = new \Magento\Catalog\Model\Product\Option\Validator\Pool(
            ['default' => $this->defaultValidatorMock, 'select' => $this->selectValidatorMock]
        );
    }

    public function testGetSelectValidator()
    {
        $this->assertEquals($this->selectValidatorMock, $this->pool->get('select'));
    }

    public function testGetDefaultValidator()
    {
        $this->assertEquals($this->defaultValidatorMock, $this->pool->get('default'));
    }
}
