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
namespace Magento\Backend\Model\Config\Backend\Admin;

/**
 * @magentoAppArea adminhtml
 */
class RobotsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Backend\Admin\Robots
     */
    protected $model = null;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    protected $rootDirectory;

    /**
     * Initialize model
     */
    protected function setUp()
    {
        parent::setUp();

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->create('Magento\Backend\Model\Config\Backend\Admin\Robots');
        $this->model->setPath('design/search_engine_robots/custom_instructions');
        $this->model->afterLoad();
        $this->rootDirectory = $objectManager->get(
            'Magento\Framework\App\Filesystem'
        )->getDirectoryRead(
            \Magento\Framework\App\Filesystem::ROOT_DIR
        );
    }

    /**
     * Check that default value is empty when robots.txt not exists
     *
     * @magentoDataFixture Magento/Backend/Model/_files/no_robots_txt.php
     */
    public function testAfterLoadRobotsTxtNotExists()
    {
        $this->assertEmpty($this->model->getValue());
    }

    /**
     * Check that default value equals to robots.txt content when it is available
     *
     * @magentoDataFixture Magento/Backend/Model/_files/robots_txt.php
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
     * @magentoDataFixture Magento/Backend/Model/_files/robots_txt.php
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
    protected function tearDown()
    {
        require 'Magento/Backend/Model/_files/no_robots_txt.php';
    }
}
