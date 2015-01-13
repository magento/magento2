<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Email\Model\Template|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * @var \Zend_Mail|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_mail;

    protected function setUp()
    {
        $this->_mail = $this->getMock(
            'Zend_Mail',
            ['send', 'addTo', 'addBcc', 'setReturnPath', 'setReplyTo'],
            ['utf-8']
        );
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->getMockBuilder(
            'Magento\Email\Model\Template'
        )->setMethods(
            ['_getMail']
        )->setConstructorArgs(
            [
                $objectManager->get('Magento\Framework\Model\Context'),
                $objectManager->get('Magento\Framework\View\DesignInterface'),
                $objectManager->get('Magento\Framework\Registry'),
                $objectManager->get('Magento\Core\Model\App\Emulation'),
                $objectManager->get('Magento\Store\Model\StoreManager'),
                $objectManager->create('Magento\Framework\Filesystem'),
                $objectManager->create('Magento\Framework\View\Asset\Repository'),
                $objectManager->create('Magento\Framework\View\FileSystem'),
                $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface'),
                $objectManager->get('Magento\Email\Model\Template\FilterFactory'),
                $objectManager->get('Magento\Email\Model\Template\Config')
            ]
        )->getMock();
        $objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');
        $this->_model->expects($this->any())->method('_getMail')->will($this->returnCallback([$this, 'getMail']));
        $this->_model->setSenderName('sender')->setSenderEmail('sender@example.com')->setTemplateSubject('Subject');
    }

    /**
     * Return a disposable \Zend_Mail instance
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Zend_Mail
     */
    public function getMail()
    {
        return clone $this->_mail;
    }

    public function testSetGetTemplateFilter()
    {
        $filter = $this->_model->getTemplateFilter();
        $this->assertSame($filter, $this->_model->getTemplateFilter());
        $this->assertEquals(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\Store\Model\StoreManagerInterface'
            )->getStore()->getId(),
            $filter->getStoreId()
        );

        $filter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Email\Model\Template\Filter'
        );
        $this->_model->setTemplateFilter($filter);
        $this->assertSame($filter, $this->_model->getTemplateFilter());
    }

    public function testLoadDefault()
    {
        $this->_model->loadDefault('customer_create_account_email_template');
        $this->assertNotEmpty($this->_model->getTemplateText());
        $this->assertNotEmpty($this->_model->getTemplateSubject());
        $this->assertNotEmpty($this->_model->getOrigTemplateVariables());
        $this->assertInternalType('array', \Zend_Json::decode($this->_model->getOrigTemplateVariables()));
        $this->assertNotEmpty($this->_model->getTemplateStyles());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Core/_files/store.php
     */
    public function testGetProcessedTemplate()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\AreaList'
        )->getArea(
            \Magento\Framework\App\Area::AREA_FRONTEND
        )->load();
        $this->_setNotDefaultThemeForFixtureStore();
        $expectedViewUrl = 'static/frontend/Magento/luma/en_US/Magento_Theme/favicon.ico';
        $this->_model->setTemplateText('{{view url="Magento_Theme::favicon.ico"}}');
        $this->assertStringEndsNotWith($expectedViewUrl, $this->_model->getProcessedTemplate());
        $this->_model->setDesignConfig(
            [
                'area' => 'frontend',
                'store' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Store\Model\StoreManagerInterface'
                )->getStore(
                    'fixturestore'
                )->getId()
            ]
        );
        $this->assertStringEndsWith($expectedViewUrl, $this->_model->getProcessedTemplate());
    }

    /**
     * Set 'Magento/luma' for the 'fixturestore' store.
     * Application isolation is required, if a test uses this method.
     */
    protected function _setNotDefaultThemeForFixtureStore()
    {
        $theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Design\ThemeInterface'
        );
        $theme->load('Magento/luma', 'theme_path');
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\Config\MutableScopeConfigInterface'
        )->setValue(
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
            $theme->getId(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            'fixturestore'
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Core/_files/store.php
     */
    public function testGetProcessedTemplateSubject()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\AreaList'
        )->getArea(
            \Magento\Framework\App\Area::AREA_FRONTEND
        )->load();
        $this->_setNotDefaultThemeForFixtureStore();
        $expectedViewUrl = 'static/frontend/Magento/luma/en_US/Magento_Theme/favicon.ico';
        $this->_model->setTemplateSubject('{{view url="Magento_Theme::favicon.ico"}}');
        $this->assertStringEndsNotWith($expectedViewUrl, $this->_model->getProcessedTemplateSubject([]));
        $this->_model->setDesignConfig(
            [
                'area' => 'frontend',
                'store' => \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Store\Model\StoreManagerInterface'
                )->getStore(
                    'fixturestore'
                )->getId()
            ]
        );
        $this->assertStringEndsWith($expectedViewUrl, $this->_model->getProcessedTemplateSubject([]));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetDefaultEmailLogo()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\AreaList'
        )->getArea(
            \Magento\Framework\App\Area::AREA_FRONTEND
        )->load();
        $this->assertStringEndsWith(
            'static/frontend/Magento/blank/en_US/Magento_Email/logo_email.gif',
            $this->_model->getDefaultEmailLogo()
        );
    }

    /**
     * @dataProvider setDesignConfigExceptionDataProvider
     * @expectedException \Magento\Framework\Exception
     */
    public function testSetDesignConfigException($config)
    {
        // \Magento\Core\Model\Template is an abstract class
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Email\Model\Template');
        $model->setDesignConfig($config);
    }

    public function setDesignConfigExceptionDataProvider()
    {
        $storeId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        return [
            [[]],
            [['area' => 'frontend']],
            [['store' => $storeId]],
        ];
    }
}
