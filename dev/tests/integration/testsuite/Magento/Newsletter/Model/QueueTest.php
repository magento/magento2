<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model;

use Magento\Store\Model\ScopeInterface;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Newsletter/_files/queue.php
     * @magentoAppIsolation enabled
     */
    public function testSendPerSubscriber()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableConfig */
        $mutableConfig = $objectManager->get('Magento\Framework\App\Config\MutableScopeConfigInterface');
        $mutableConfig->setValue('general/locale/code', 'de_DE', ScopeInterface::SCOPE_STORE, 'fixturestore');

        $objectManager->get('Magento\Framework\App\State')->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $area = $objectManager->get('Magento\Framework\App\AreaList')
            ->getArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        $area->load();

        /** @var $filter \Magento\Newsletter\Model\Template\Filter */
        $filter = $objectManager->get('Magento\Newsletter\Model\Template\Filter');

        $transport = $this->getMock('\Magento\Framework\Mail\TransportInterface');
        $transport->expects($this->exactly(2))->method('sendMessage')->will($this->returnSelf());

        $builder = $this->getMock(
            '\Magento\Newsletter\Model\Queue\TransportBuilder',
            ['getTransport', 'setFrom', 'addTo'],
            [],
            '',
            false
        );
        $builder->expects($this->exactly(2))->method('getTransport')->will($this->returnValue($transport));
        $builder->expects($this->exactly(2))->method('setFrom')->will($this->returnSelf());
        $builder->expects($this->exactly(2))->method('addTo')->will($this->returnSelf());

        /** @var $queue \Magento\Newsletter\Model\Queue */
        $queue = $objectManager->create(
            'Magento\Newsletter\Model\Queue',
            ['filter' => $filter, 'transportBuilder' => $builder]
        );
        $queue->load('Subject', 'newsletter_subject');
        // fixture
        $queue->sendPerSubscriber();
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/queue.php
     * @magentoAppIsolation enabled
     */
    public function testSendPerSubscriberProblem()
    {
        $errorMsg = md5(microtime());

        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $transport = $this->getMock('\Magento\Framework\Mail\TransportInterface');
        $transport->expects($this->any())
            ->method('sendMessage')
            ->willThrowException(new \Magento\Framework\Exception\MailException(__($errorMsg)));

        $builder = $this->getMock(
            '\Magento\Newsletter\Model\Queue\TransportBuilder',
            ['getTransport', 'setFrom', 'addTo', 'setTemplateOptions', 'setTemplateVars'],
            [],
            '',
            false
        );
        $builder->expects($this->any())->method('getTransport')->will($this->returnValue($transport));
        $builder->expects($this->any())->method('setTemplateOptions')->will($this->returnSelf());
        $builder->expects($this->any())->method('setTemplateVars')->will($this->returnSelf());
        $builder->expects($this->any())->method('setFrom')->will($this->returnSelf());
        $builder->expects($this->any())->method('addTo')->will($this->returnSelf());

        /** @var $queue \Magento\Newsletter\Model\Queue */
        $queue = $objectManager->create('Magento\Newsletter\Model\Queue', ['transportBuilder' => $builder]);
        $queue->load('Subject', 'newsletter_subject');
        // fixture

        $problem = $objectManager->create('Magento\Newsletter\Model\Problem');
        $problem->load($queue->getId(), 'queue_id');
        $this->assertEmpty($problem->getId());

        $queue->sendPerSubscriber();

        $problem->load($queue->getId(), 'queue_id');
        $this->assertNotEmpty($problem->getId());
        $this->assertEquals($errorMsg, $problem->getProblemErrorText());
    }
}
