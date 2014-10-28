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
namespace Magento\Core\Model\Theme\Domain;

use Magento\Framework\View\Design\ThemeInterface;

class VirtualTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $_themes = array(
        'physical' => array(
            'parent_id' => null,
            'theme_path' => 'test/test',
            'theme_version' => '0.1.0',
            'theme_title' => 'Test physical theme',
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'type' => ThemeInterface::TYPE_PHYSICAL
        ),
        'virtual' => array(
            'parent_id' => null,
            'theme_path' => '',
            'theme_version' => '0.1.0',
            'theme_title' => 'Test virtual theme',
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'type' => ThemeInterface::TYPE_VIRTUAL
        ),
        'staging' => array(
            'parent_id' => null,
            'theme_path' => '',
            'theme_version' => '0.1.0',
            'theme_title' => 'Test staging theme',
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'type' => ThemeInterface::TYPE_STAGING
        )
    );

    /**
     * @var int
     */
    protected $_physicalThemeId;

    /**
     * @var int
     */
    protected $_virtualThemeId;

    /**
     * @magentoDbIsolation enabled
     */
    public function testGetPhysicalTheme()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        //1. set up fixture
        /** @var $physicalTheme \Magento\Framework\View\Design\ThemeInterface */
        $physicalTheme = $objectManager->create('Magento\Framework\View\Design\ThemeInterface');
        $physicalTheme->setData($this->_themes['physical']);
        $physicalTheme->save();

        $this->_themes['virtual']['parent_id'] = $physicalTheme->getId();

        /** @var $virtualTheme \Magento\Framework\View\Design\ThemeInterface */
        $virtualTheme = $objectManager->create('Magento\Framework\View\Design\ThemeInterface');
        $virtualTheme->setData($this->_themes['virtual']);
        $virtualTheme->save();

        $this->_themes['staging']['parent_id'] = $virtualTheme->getId();

        /** @var $stagingTheme \Magento\Framework\View\Design\ThemeInterface */
        $stagingTheme = $objectManager->create('Magento\Framework\View\Design\ThemeInterface');
        $stagingTheme->setData($this->_themes['staging']);
        $stagingTheme->save();

        $this->_physicalThemeId = $physicalTheme->getId();
        $this->_virtualThemeId = $virtualTheme->getId();

        //2. run test
        /** @var $virtualTheme \Magento\Framework\View\Design\ThemeInterface */
        $virtualTheme = $objectManager->create('Magento\Framework\View\Design\ThemeInterface');
        $virtualTheme->load($this->_virtualThemeId);

        $this->assertEquals(
            $this->_physicalThemeId,
            $virtualTheme->getDomainModel(
                \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL
            )->getPhysicalTheme()->getId()
        );
    }

    protected function tearDown()
    {
        unset($this->_physicalThemeId);
        unset($this->_virtualThemeId);
    }
}
