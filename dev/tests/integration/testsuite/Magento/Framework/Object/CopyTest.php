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
namespace Magento\Framework\Object;

class CopyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Object\Copy
     */
    protected $_service;

    protected function setUp()
    {
        $this->_service = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Object\Copy');
    }

    public function testCopyFieldset()
    {
        $fieldset = 'sales_copy_order';
        $aspect = 'to_edit';
        $data = array('customer_email' => 'admin@example.com', 'customer_group_id' => '1');
        $source = new \Magento\Framework\Object($data);
        $target = new \Magento\Framework\Object();
        $expectedTarget = new \Magento\Framework\Object($data);
        $expectedTarget->setDataChanges(true);
        // hack for assertion

        $this->assertNull($this->_service->copyFieldsetToTarget($fieldset, $aspect, 'invalid_source', array()));
        $this->assertNull($this->_service->copyFieldsetToTarget($fieldset, $aspect, array(), 'invalid_target'));
        $this->assertEquals(
            $target,
            $this->_service->copyFieldsetToTarget('invalid_fieldset', $aspect, $source, $target)
        );
        $this->assertSame($target, $this->_service->copyFieldsetToTarget($fieldset, $aspect, $source, $target));
        $this->assertEquals($expectedTarget, $target);
    }

    public function testCopyFieldsetArrayTarget()
    {
        $fieldset = 'sales_copy_order';
        $aspect = 'to_edit';
        $data = array('customer_email' => 'admin@example.com', 'customer_group_id' => '1');
        $source = new \Magento\Framework\Object($data);
        $target = array();
        $expectedTarget = $data;

        $this->assertEquals(
            $target,
            $this->_service->copyFieldsetToTarget('invalid_fieldset', $aspect, $source, $target)
        );
        $this->assertEquals(
            $expectedTarget,
            $this->_service->copyFieldsetToTarget($fieldset, $aspect, $source, $target)
        );
    }
}
