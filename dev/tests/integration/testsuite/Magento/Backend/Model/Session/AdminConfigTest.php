<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Session;

use PHPUnit_Framework_TestCase;

/**
 * Test class for \Magento\Backend\Model\Session\AdminConfig.
 *
 * @magentoAppArea adminhtml
 */
class AdminConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected function setUp()
    {
        parent::setUp();

        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    public function testConstructor()
    {
        $model = $this->objectManager->create('Magento\Backend\Model\Session\AdminConfig');
        $this->assertEquals('/index.php/backend', $model->getCookiePath());
    }

    /**
     * Test for setting session name for admin
     *
     */
    public function testSetSessionNameByConstructor()
    {
        $sessionName = 'adminHtmlSession';
        $adminConfig = $this->objectManager->create(
            'Magento\Backend\Model\Session\AdminConfig',
            ['sessionName' => $sessionName]
        );
        $this->assertSame($sessionName, $adminConfig->getName());
    }
}
