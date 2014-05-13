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
namespace Magento\Framework\Model\Resource\Entity;

class TableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\Resource\Entity\Table
     */
    protected $_model;

    protected function setUp()
    {
        // @codingStandardsIgnoreStart
        $config = new \Magento\Framework\Simplexml\Config();
        $config->table = 'test_table';
        $config->test_key = 'test';
        // @codingStandardsIgnoreEnd

        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Model\Resource\Entity\Table', array('config' => $config));
    }

    public function testGetTable()
    {
        $this->assertEquals('test_table', $this->_model->getTable());
    }

    public function testGetConfig()
    {
        $this->assertInstanceOf('Magento\Framework\Simplexml\Config', $this->_model->getConfig());
        $this->assertEquals('test', $this->_model->getConfig('test_key'));
        $this->assertFalse($this->_model->getConfig('some_key'));
    }
}
