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

/**
 * Test class for \Magento\ImportExport\Model\Source\Import\Behavior\Basic
 */
namespace Magento\ImportExport\Model\Source\Import\Behavior;

class BasicTest extends \Magento\ImportExport\Model\Source\Import\AbstractBehaviorTestCase
{
    /**
     * Expected behavior group code
     *
     * @var string
     */
    protected $_expectedCode = 'basic';

    /**
     * Expected behaviours
     *
     * @var array
     */
    protected $_expectedBehaviors = array(
        \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND,
        \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE,
        \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE
    );

    protected function setUp()
    {
        parent::setUp();
        $this->_model = new \Magento\ImportExport\Model\Source\Import\Behavior\Basic();
    }

    /**
     * Test toArray method
     *
     * @covers \Magento\ImportExport\Model\Source\Import\Behavior\Basic::toArray
     */
    public function testToArray()
    {
        $behaviorData = $this->_model->toArray();
        $this->assertInternalType('array', $behaviorData);
        $this->assertEquals($this->_expectedBehaviors, array_keys($behaviorData));
    }

    /**
     * Test behavior group code
     *
     * @covers \Magento\ImportExport\Model\Source\Import\Behavior\Basic::getCode
     */
    public function testGetCode()
    {
        $this->assertEquals($this->_expectedCode, $this->_model->getCode());
    }
}
