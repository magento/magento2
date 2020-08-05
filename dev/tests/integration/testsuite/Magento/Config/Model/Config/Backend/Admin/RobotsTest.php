<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Backend\Admin;

use Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;

/**
 * @magentoAppArea adminhtml
 */
class RobotsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Config\Model\Config\Backend\Admin\Robots
     */
    protected $model = null;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    protected $rootDirectory;

    /**
     * Initialize model
     */
    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->create(\Magento\Config\Model\Config\Backend\Admin\Robots::class);
        $this->model->setPath('design/search_engine_robots/custom_instructions');
        $this->model->afterLoad();

        $documentRootPath = $objectManager->get(DocumentRoot::class)->getPath();
        $this->rootDirectory = $objectManager->get(
            \Magento\Framework\Filesystem::class
        )->getDirectoryRead($documentRootPath);
    }

    /**
     * Check that default value is empty when robots.txt not exists
     *
     * @magentoDataFixture Magento/Config/Model/_files/no_robots_txt.php
     */
    public function testAfterLoadRobotsTxtNotExists()
    {
        $this->assertEmpty($this->model->getValue());
    }

    /**
     * Check that default value equals to robots.txt content when it is available
     *
     * @magentoDataFixture Magento/Config/Model/_files/robots_txt.php
     */
    public function testAfterLoadRobotsTxtExists()
    {
        $this->assertEquals('Sitemap: http://store.com/sitemap.xml', $this->model->getValue());
    }

    /**
     * Check robots.txt file generated when robots.txt not exists
     *
     * @magentoDbIsolation enabled
     */
    public function testAfterSaveFileNotExists()
    {
        $this->assertFalse($this->rootDirectory->isExist('robots.txt'), 'robots.txt exists');

        $this->_modifyConfig();
    }

    /**
     * Check robots.txt file changed when robots.txt exists
     *
     * @magentoDataFixture Magento/Config/Model/_files/robots_txt.php
     * @magentoDbIsolation enabled
     */
    public function testAfterSaveFileExists()
    {
        $this->assertTrue($this->rootDirectory->isExist('robots.txt'), 'robots.txt not exists');

        $this->_modifyConfig();
    }

    /**
     * Modify config value and check all changes were written into robots.txt
     */
    protected function _modifyConfig()
    {
        $robotsTxt = "User-Agent: *\nDisallow: /checkout";
        $this->model->setValue($robotsTxt)->save();
        $this->assertStringEqualsFile($this->rootDirectory->getAbsolutePath('robots.txt'), $robotsTxt);
    }

    /**
     * Remove created robots.txt
     */
    protected function tearDown(): void
    {
        require 'Magento/Config/Model/_files/no_robots_txt.php';
    }
}
