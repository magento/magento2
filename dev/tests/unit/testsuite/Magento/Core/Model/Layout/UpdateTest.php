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
namespace Magento\Core\Model\Layout;

class UpdateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test formatted time data
     */
    const TEST_FORMATTED_TIME = 'test_time';

    public function testBeforeSave()
    {
        $resourceModel = $this->getMock(
            'Magento\Core\Model\Resource\Layout\Update',
            array(
                '__wakeup',
                'formatDate',
                'getIdFieldName',
                'beginTransaction',
                'save',
                'addCommitCallback',
                'commit'
            ),
            array(),
            '',
            false
        );
        $resourceModel->expects($this->once())->method('addCommitCallback')->will($this->returnSelf());
        $dateTime = $this->getMock('\Magento\Framework\Stdlib\DateTime', array(), array());
        $dateTime->expects(
            $this->once()
        )->method(
            'formatDate'
        )->with(
            $this->isType('int')
        )->will(
            $this->returnValue(self::TEST_FORMATTED_TIME)
        );

        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        /** @var $model \Magento\Core\Model\Layout\Update */
        $model = $helper->getObject(
            'Magento\Core\Model\Layout\Update',
            array('resource' => $resourceModel, 'dateTime' => $dateTime)
        );
        $model->setId(0);
        // set any data to set _hasDataChanges flag
        $model->save();

        $this->assertEquals(self::TEST_FORMATTED_TIME, $model->getUpdatedAt());
    }
}
