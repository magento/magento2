<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model;

use Magento\Store\Model\ScopeInterface;

class QueueTest extends \PHPUnit\Framework\TestCase
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
        $mutableConfig = $objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class);
        $mutableConfig->setValue('general/locale/code', 'de_DE', ScopeInterface::SCOPE_STORE, 'fixturestore');

        $objectManager->get(
            \Magento\Framework\App\State::class
        )->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $area = $objectManager->get(\Magento\Framework\App\AreaList::class)
            ->getArea(\Magento\Framework\App\Area::AREA_FRONTEND);
        $area->load();

        /** @var $filter \Magento\Newsletter\Model\Template\Filter */
        $filter = $objectManager->get(\Magento\Newsletter\Model\Template\Filter::class);

        $transport = $this->getMockBuilder(\Magento\Framework\Mail\TransportInterface::class)
            ->onlyMethods(['sendMessage'])
            ->getMockForAbstractClass();
        $transport->expects($this->exactly(2))->method('sendMessage')->willReturnSelf();

        $builder = $this->createPartialMock(
            \Magento\Newsletter\Model\Queue\TransportBuilder::class,
            ['getTransport', 'setFrom', 'addTo']
        );
        $builder->expects($this->exactly(2))->method('getTransport')->willReturn($transport);
        $builder->expects($this->exactly(2))->method('setFrom')->willReturnSelf();
        $builder->expects($this->exactly(2))->method('addTo')->willReturnSelf();

        /** @var $queue \Magento\Newsletter\Model\Queue */
        $queue = $objectManager->create(
            \Magento\Newsletter\Model\Queue::class,
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
        // md5 used here only for random string generation for test purposes. No cryptographic use.
        // phpcs:ignore Magento2.Security.InsecureFunction
        $errorMsg = md5(microtime());

        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Framework\App\Area::AREA_FRONTEND);

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $transport = $this->getMockBuilder(\Magento\Framework\Mail\TransportInterface::class)
            ->onlyMethods(['sendMessage'])
            ->getMockForAbstractClass();
        $transport->expects($this->any())
            ->method('sendMessage')
            ->willThrowException(new \Magento\Framework\Exception\MailException(__($errorMsg)));

        $builder = $this->createPartialMock(
            \Magento\Newsletter\Model\Queue\TransportBuilder::class,
            ['getTransport', 'setFrom', 'addTo', 'setTemplateOptions', 'setTemplateVars']
        );
        $builder->expects($this->any())->method('getTransport')->willReturn($transport);
        $builder->expects($this->any())->method('setTemplateOptions')->willReturnSelf();
        $builder->expects($this->any())->method('setTemplateVars')->willReturnSelf();
        $builder->expects($this->any())->method('setFrom')->willReturnSelf();
        $builder->expects($this->any())->method('addTo')->willReturnSelf();

        /** @var $queue \Magento\Newsletter\Model\Queue */
        $queue = $objectManager->create(\Magento\Newsletter\Model\Queue::class, ['transportBuilder' => $builder]);
        $queue->load('Subject', 'newsletter_subject');
        // fixture

        $problem = $objectManager->create(\Magento\Newsletter\Model\Problem::class);
        $problem->load($queue->getId(), 'queue_id');
        $this->assertEmpty($problem->getId());

        $queue->sendPerSubscriber();

        $problem->load($queue->getId(), 'queue_id');
        $this->assertNotEmpty($problem->getId());
        $this->assertEquals($errorMsg, $problem->getProblemErrorText());
    }
}
