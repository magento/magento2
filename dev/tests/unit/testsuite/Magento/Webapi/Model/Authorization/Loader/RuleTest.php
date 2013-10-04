<?php
/**
 * Test class for \Magento\Webapi\Model\Authorization\Loader\Rule
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Authorization\Loader;

class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Webapi\Model\Resource\Acl\Rule
     */
    protected $_ruleResource;

    /**
     * @var \Magento\Webapi\Model\Authorization\Loader\Rule
     */
    protected $_model;

    /**
     * @var \Magento\Acl
     */
    protected $_acl;

    /**
     * Set up before test
     */
    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_ruleResource = $this->getMock('Magento\Webapi\Model\Resource\Acl\Rule',
            array('getRuleList'), array(), '', false);

        $this->_acl = $this->getMock('Magento\Acl', array('has', 'hasRole', 'allow'), array(), '', false);

        $this->_model = $helper->getObject('Magento\Webapi\Model\Authorization\Loader\Rule', array(
            'ruleResource' => $this->_ruleResource,
        ));
    }

    /**
     * Test for \Magento\Webapi\Model\Authorization\Loader\Rule::populateAcl.
     */
    public function testPopulateAcl()
    {
        $ruleList = array(
            array('role_id' => 5, 'resource_id' => 7),
            array('role_id' => 5, 'resource_id' => 8),
            array('role_id' => 6, 'resource_id' => 7)
        );
        $hasMap = array(
            array(7, true),
            array(8, false)
        );
        $hasRoleMap = array(
            array(5, true),
            array(6, false),
            array(5, true)
        );

        $this->_ruleResource->expects($this->once())
            ->method('getRuleList')
            ->will($this->returnValue($ruleList));

        $this->_acl->expects($this->exactly(count($hasMap)))
            ->method('has')
            ->with($this->logicalOr(7, 8))
            ->will($this->returnValueMap($hasMap));
        $this->_acl->expects($this->exactly(count($hasRoleMap)))
            ->method('hasRole')
            ->with($this->logicalOr(5, 6))
            ->will($this->returnValueMap($hasRoleMap));
        $this->_acl->expects($this->once())
            ->method('allow')
            ->with(5, 7);

        $this->_model->populateAcl($this->_acl);
    }

    /**
     * Test for \Magento\Webapi\Model\Authorization\Loader\Rule::populateAcl without rules.
     */
    public function testPopulateAclWithoutRules()
    {
        $this->_ruleResource->expects($this->once())
            ->method('getRuleList')
            ->will($this->returnValue(array()));

        $this->_acl->expects($this->never())
            ->method('has');
        $this->_acl->expects($this->never())
            ->method('hasRole');
        $this->_acl->expects($this->never())
            ->method('allow');

        $this->_model->populateAcl($this->_acl);
    }
}
