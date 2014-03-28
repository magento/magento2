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
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Model\Resource;

class IteratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Model\Resource\Iterator
     */
    protected $_model;

    /**
     * Counter for testing walk() callback
     *
     * @var int
     */
    protected $_callbackCounter = 0;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Model\Resource\Iterator');
    }

    /**
     * Helper callback for testWalk()
     *
     * @param array $data
     * @return bool
     */
    public function walkCallback($data)
    {
        $this->_callbackCounter = $data['idx'];
        return true;
    }

    /**
     * @expectedException \Magento\Model\Exception
     */
    public function testWalkException()
    {
        $this->_model->walk('test', array(array($this, 'walkCallback')));
    }
}
