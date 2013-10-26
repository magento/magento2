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
 * @package     Magento_Backend
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Model;

/**
 * @magentoAppArea adminhtml
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Backend\Model\Config::save
     * @param array $groups
     * @magentoDbIsolation enabled
     * @dataProvider saveWithSingleStoreModeEnabledDataProvider
     * @magentoConfigFixture current_store general/single_store_mode/enabled 1
     */
    public function testSaveWithSingleStoreModeEnabled($groups)
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Config\ScopeInterface')
            ->setCurrentScope(\Magento\Core\Model\App\Area::AREA_ADMINHTML);
        /** @var $_configDataObject \Magento\Backend\Model\Config */
        $_configDataObject = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Backend\Model\Config');
        $_configData = $_configDataObject->setSection('dev')
            ->setWebsite('base')
            ->load();
        $this->assertEmpty($_configData);

        $_configDataObject = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Backend\Model\Config');
        $_configDataObject->setSection('dev')
            ->setGroups($groups)
            ->save();

        /** @var $_configDataObject \Magento\Backend\Model\Config */
        $_configDataObject = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Backend\Model\Config');
        $_configDataObject->setSection('dev')
            ->setWebsite('base');

        $_configData = $_configDataObject->load();
        $this->assertArrayHasKey('dev/debug/template_hints', $_configData);
        $this->assertArrayHasKey('dev/debug/template_hints_blocks', $_configData);

        $_configDataObject = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Backend\Model\Config');
        $_configDataObject->setSection('dev');
        $_configData = $_configDataObject->load();
        $this->assertArrayNotHasKey('dev/debug/template_hints', $_configData);
        $this->assertArrayNotHasKey('dev/debug/template_hints_blocks', $_configData);
    }

    public function saveWithSingleStoreModeEnabledDataProvider()
    {
        return require(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'config_groups.php');
    }

    /**
     * @covers \Magento\Backend\Model\Config::save
     * @param string $section
     * @param array $groups
     * @param array $expected
     * @magentoDbIsolation enabled
     * @dataProvider saveDataProvider
     */
    public function testSave($section, $groups, $expected)
    {
        /** @var $_configDataObject \Magento\Backend\Model\Config */
        $_configDataObject = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Backend\Model\Config');
        $_configDataObject->setSection($section)
            ->setWebsite('base')
            ->setGroups($groups)
            ->save();

        foreach ($expected as $group => $expectedData) {
            $_configDataObject = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->create('Magento\Backend\Model\Config');
            $_configData = $_configDataObject->setSection($group)->setWebsite('base')
                ->load();
            if (array_key_exists('payment/payflow_link/pwd', $_configData)) {
                $_configData['payment/payflow_link/pwd'] =
                    \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Helper\Data')
                        ->decrypt($_configData['payment/payflow_link/pwd']);
            }
            $this->assertEquals($expectedData, $_configData);
        }
    }

    public function saveDataProvider()
    {
        return require(__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'config_section.php');
    }
}
