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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Backend_Model_Config_Backend_SecureTest extends PHPUnit_Framework_TestCase
{
    public function testSaveMergedJsCssMustBeCleaned()
    {
        $eventDispatcher = $this->getMock('Mage_Core_Model_Event_Manager', array(), array(), '', false);
        $cacheManager = $this->getMock('Mage_Core_Model_CacheInterface');
        $context = new Mage_Core_Model_Context($eventDispatcher, $cacheManager);

        $resource = $this->getMock('Mage_Core_Model_Resource_Config_Data', array(), array(), '', false);
        $resource->expects($this->any())
            ->method('addCommitCallback')
            ->will($this->returnValue($resource));
        $resourceCollection = $this->getMock('Varien_Data_Collection_Db', array(), array(), '', false);
        $mergeService = $this->getMock('Mage_Core_Model_Page_Asset_MergeService', array(), array(), '', false);

        $model = $this->getMock(
            'Mage_Backend_Model_Config_Backend_Secure',
            array('getOldValue'),
            array($context, $mergeService, $resource, $resourceCollection)
        );
        $mergeService->expects($this->once())
            ->method('cleanMergedJsCss');

        $model->setValue('new_value');
        $model->save();
    }
}
