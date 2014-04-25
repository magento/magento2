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
namespace Magento\CatalogRule\Model\Rule;

class JobTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for method applyAll
     *
     * Checks that dispatch event with param value "catalogrule_apply_all" runs while applying all rules
     */
    public function testApplyAll()
    {
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', array(), array(), '', false);
        $eventManager->expects($this->once())->method('dispatch')->with($this->equalTo('catalogrule_apply_all'));

        /** @var $jobModel \Magento\CatalogRule\Model\Rule\Job */
        $jobModel = $objectManagerHelper->getObject(
            'Magento\CatalogRule\Model\Rule\Job',
            array('eventManager' => $eventManager)
        );

        $jobModel->applyAll();
    }
}
