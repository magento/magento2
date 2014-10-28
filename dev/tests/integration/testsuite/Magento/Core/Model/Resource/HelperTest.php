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
namespace Magento\Core\Model\Resource;

class HelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Helper
     */
    protected $_model;

    /**
     * @var \Magento\Framework\DB\Select
     */
    protected $_select;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\DB\Helper',
            array('modulePrefix' => 'core')
        );
        $collection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Store\Model\Resource\Store\Collection'
        );
        $this->_select = $collection->getSelect();
    }

    public function testPrepareColumnsList()
    {
        $columns = $this->_model->prepareColumnsList($this->_select);
        $this->assertContains('STORE_ID', array_keys($columns));
    }

    public function testAddGroupConcatColumn()
    {
        $select = (string)$this->_model->addGroupConcatColumn($this->_select, 'test_alias', 'store_id');
        $this->assertContains('GROUP_CONCAT', $select);
        $this->assertContains('test_alias', $select);
    }

    public function testGetDateDiff()
    {
        $diff = $this->_model->getDateDiff('2011-01-01', '2011-01-01');
        $this->assertInstanceOf('Zend_Db_Expr', $diff);
        $this->assertContains('TO_DAYS', (string)$diff);
    }

    public function testAddLikeEscape()
    {
        $value = $this->_model->addLikeEscape('test');
        $this->assertInstanceOf('Zend_Db_Expr', $value);
        $this->assertContains('test', (string)$value);
    }
}
