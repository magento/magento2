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
 * @package     Magento_Newsletter
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Newsletter\Model;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Newsletter/_files/queue.php
     * @magentoConfigFixture fixturestore_store general/locale/code de_DE
     * @magentoAppIsolation enabled
     */
    public function testSendPerSubscriber()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var $app \Magento\TestFramework\App */
        $app = $objectManager->get('Magento\Core\Model\App');
        $app->loadArea(\Magento\Core\Model\App\Area::AREA_FRONTEND);

        /** @var $filter \Magento\Newsletter\Model\Template\Filter */
        $filter = $objectManager->get('Magento\Newsletter\Model\Template\Filter');

        $transport = $this->getMock('\Magento\Mail\TransportInterface');
        $transport->expects($this->exactly(2))
            ->method('sendMessage')
            ->will($this->returnSelf());

        $builder = $this->getMock('\Magento\Newsletter\Model\Queue\TransportBuilder',
            array('getTransport', 'setFrom', 'addTo'),
            array(), '', false);
        $builder->expects($this->exactly(2))
            ->method('getTransport')
            ->will($this->returnValue($transport));
        $builder->expects($this->exactly(2))
            ->method('setFrom')
            ->will($this->returnSelf());
        $builder->expects($this->exactly(2))
            ->method('addTo')
            ->will($this->returnSelf());

        /** @var $queue \Magento\Newsletter\Model\Queue */
        $queue = $objectManager->create('Magento\Newsletter\Model\Queue', array(
            'filter' => $filter,
            'transportBuilder' => $builder,
        ));
        $queue->load('Subject', 'newsletter_subject'); // fixture
        $queue->sendPerSubscriber();
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/queue.php
     * @magentoAppIsolation enabled
     */
    public function testSendPerSubscriberProblem()
    {
        $errorMsg = md5(microtime());

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get('Magento\Core\Model\App')
            ->loadArea(\Magento\Core\Model\App\Area::AREA_FRONTEND);

        $transport = $this->getMock('\Magento\Mail\TransportInterface');
        $transport->expects($this->any())
            ->method('sendMessage')
            ->will($this->throwException(new \Magento\Mail\Exception($errorMsg, 99)));

        $builder = $this->getMock('\Magento\Newsletter\Model\Queue\TransportBuilder',
            array('getTransport', 'setFrom', 'addTo'),
            array(), '', false);
        $builder->expects($this->any())
            ->method('getTransport')
            ->will($this->returnValue($transport));
        $builder->expects($this->any())
            ->method('setFrom')
            ->will($this->returnSelf());
        $builder->expects($this->any())
            ->method('addTo')
            ->will($this->returnSelf());

        /** @var $queue \Magento\Newsletter\Model\Queue */
        $queue = $objectManager->create('Magento\Newsletter\Model\Queue', array(
            'transportBuilder' => $builder,
        ));
        $queue->load('Subject', 'newsletter_subject'); // fixture

        $problem = $objectManager->create('Magento\Newsletter\Model\Problem');
        $problem->load($queue->getId(), 'queue_id');
        $this->assertEmpty($problem->getId());


        $queue->sendPerSubscriber();

        $problem->load($queue->getId(), 'queue_id');
        $this->assertNotEmpty($problem->getId());
        $this->assertEquals(99, $problem->getProblemErrorCode());
        $this->assertEquals($errorMsg, $problem->getProblemErrorText());
    }
}
