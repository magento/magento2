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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
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

        $themes = array('frontend' => 'magento_blank');
        /** @var $design \Magento\Core\Model\View\Design */
        $design = $objectManager->create('Magento\View\DesignInterface', array('themes' => $themes));
        $objectManager->addSharedInstance($design, 'Magento\Core\Model\View\Design');
        /** @var $appEmulation \Magento\Core\Model\App\Emulation */
        $appEmulation = $objectManager->create('Magento\Core\Model\App\Emulation', array('viewDesign' => $design));
        $objectManager->addSharedInstance($appEmulation, 'Magento\Core\Model\App\Emulation');
        /** @var $app \Magento\Core\Model\App */
        $app = $objectManager->get('Magento\Core\Model\App');
        $app->getArea(\Magento\Core\Model\App\Area::AREA_FRONTEND)->load();

        /** @var $collection \Magento\Core\Model\Resource\Theme\Collection */
        $collection = $objectManager->create('Magento\Core\Model\Resource\Theme\Collection');
        $themeId = $collection->getThemeByFullPath('frontend/magento_demo')->getId();
        /** @var $storeManager \Magento\Core\Model\StoreManagerInterface */
        $storeManager = $objectManager->get('Magento\Core\Model\StoreManagerInterface');
        $storeManager->getStore('fixturestore')->setConfig('design/theme/theme_id', $themeId);

        $subscriberOne = $this->getMock('Zend_Mail', array('send', 'setBodyHTML'), array('utf-8'));
        $subscriberOne->expects($this->any())->method('send');
        $subscriberTwo = clone $subscriberOne;
        $subscriberOne->expects($this->once())->method('setBodyHTML')->with(
            $this->stringEndsWith('/static/frontend/magento_blank/en_US/images/logo.gif')
        );
        $subscriberTwo->expects($this->once())->method('setBodyHTML')->with(
            $this->stringEndsWith('/static/frontend/magento_demo/de_DE/images/logo.gif')
        );
        /** @var $filter \Magento\Newsletter\Model\Template\Filter */
        $filter = $objectManager->get('Magento\Newsletter\Model\Template\Filter');

        $emailTemplate = $this->getMock('Magento\Core\Model\Email\Template',
            array('_getMail', '_getLogoUrl', '__wakeup', 'setTemplateFilter'),
            array(
                $objectManager->get('Magento\Core\Model\Context'),
                $objectManager->get('Magento\Core\Model\Registry'),
                $appEmulation,
                $objectManager->get('Magento\Filesystem'),
                $objectManager->get('Magento\Core\Model\View\Url'),
                $objectManager->get('Magento\Core\Model\View\FileSystem'),
                $design,
                $objectManager->get('Magento\Core\Model\Store\ConfigInterface'),
                $objectManager->get('Magento\Core\Model\ConfigInterface'),
                $objectManager->get('Magento\Core\Model\Email\Template\FilterFactory'),
                $objectManager->get('Magento\Core\Model\StoreManagerInterface'),
                $objectManager->get('Magento\App\Dir'),
                $objectManager->get('Magento\Core\Model\Email\Template\Config'),
            )
        );
        $emailTemplate->expects($this->once())
            ->method('setTemplateFilter')
            ->with($filter);

        $emailTemplate->expects($this->exactly(2))->method('_getMail')->will($this->onConsecutiveCalls(
            $subscriberOne, $subscriberTwo
        ));
        /** @var $queue \Magento\Newsletter\Model\Queue */
        $queue = $objectManager->create('Magento\Newsletter\Model\Queue', array(
            'filter' => $filter,
            'data'   => array('email_template' => $emailTemplate)
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
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\App')
            ->getArea(\Magento\Core\Model\App\Area::AREA_FRONTEND)->load();
        $mail = $this->getMock('Zend_Mail', array('send'), array('utf-8'));
        $brokenMail = $this->getMock('Zend_Mail', array('send'), array('utf-8'));
        $errorMsg = md5(microtime());
        $brokenMail->expects($this->any())->method('send')->will($this->throwException(new \Exception($errorMsg, 99)));
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $template = $this->getMock('Magento\Core\Model\Email\Template',
            array('_getMail', '_getLogoUrl', '__wakeup'),
            array(
                $objectManager->get('Magento\Core\Model\Context'),
                $objectManager->get('Magento\Core\Model\Registry'),
                $objectManager->get('Magento\Core\Model\App\Emulation'),
                $objectManager->get('Magento\Filesystem'),
                $objectManager->get('Magento\Core\Model\View\Url'),
                $objectManager->get('Magento\Core\Model\View\FileSystem'),
                $objectManager->get('Magento\Core\Model\View\Design'),
                $objectManager->get('Magento\Core\Model\Store\ConfigInterface'),
                $objectManager->get('Magento\Core\Model\ConfigInterface'),
                $objectManager->get('Magento\Core\Model\Email\Template\FilterFactory'),
                $objectManager->get('Magento\Core\Model\StoreManagerInterface'),
                $objectManager->get('Magento\App\Dir'),
                $objectManager->get('Magento\Core\Model\Email\Template\Config'),
            )
        );
        $template->expects($this->any())->method('_getMail')->will($this->onConsecutiveCalls($mail, $brokenMail));

        $storeConfig = $objectManager->get('Magento\Core\Model\Store\Config');
        $coreStoreConfig = new \ReflectionProperty($template, '_coreStoreConfig');
        $coreStoreConfig->setAccessible(true);
        $coreStoreConfig->setValue($template, $storeConfig);

        $queue = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Newsletter\Model\Queue',
            array('data' => array('email_template' => $template))
        );
        $queue->load('Subject', 'newsletter_subject'); // fixture
        $problem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Newsletter\Model\Problem');
        $problem->load($queue->getId(), 'queue_id');
        $this->assertEmpty($problem->getId());

        $queue->sendPerSubscriber();

        $problem->load($queue->getId(), 'queue_id');
        $this->assertNotEmpty($problem->getId());
        $this->assertEquals(99, $problem->getProblemErrorCode());
        $this->assertEquals($errorMsg, $problem->getProblemErrorText());
    }
}
