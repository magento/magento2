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
 * @package     Mage_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Model_Menu_Builder_Command_RemoveTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Menu_Builder_Command_Remove
     */
    protected $_model;

    protected $_params = array(
        'id' => 'item'
    );

    public function setUp()
    {
        $this->_model = new Mage_Backend_Model_Menu_Builder_Command_Remove($this->_params);
    }

    public function testExecuteMarksItemAsRemoved()
    {
        $params = $this->_model->execute(array());
        $this->_params['removed'] = true;
        $this->assertEquals($this->_params, $params);
    }
}
