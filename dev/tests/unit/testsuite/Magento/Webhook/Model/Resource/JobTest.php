<?php
/**
 * \Magento\Webhook\Model\Resource\Job
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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Resource;

class JobTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $tableName = 'webhook_dispatch_job_table';
        $idFieldName = 'dispatch_job_id';
        $resourceMock = $this->getMockBuilder('Magento\Core\Model\Resource')
            ->disableOriginalConstructor()
            ->getMock();
        $resourceMock->expects($this->once())
            ->method('getTableName')
            ->with('webhook_dispatch_job')
            ->will($this->returnValue($tableName));

        $job = new \Magento\Webhook\Model\Resource\Job ($resourceMock);
        $this->assertEquals($tableName, $job->getMainTable() );
        $this->assertEquals($idFieldName, $job->getIdFieldName());
    }
}
