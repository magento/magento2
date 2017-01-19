<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Theme\Domain;

use Magento\Framework\View\Design\ThemeInterface;

class VirtualTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $_themes = [
        'physical' => [
            'parent_id' => null,
            'theme_path' => 'test/test',
            'theme_title' => 'Test physical theme',
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'type' => ThemeInterface::TYPE_PHYSICAL,
            'code' => 'physical'
        ],
        'virtual' => [
            'parent_id' => null,
            'theme_path' => '',
            'theme_title' => 'Test virtual theme',
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'type' => ThemeInterface::TYPE_VIRTUAL,
            'code' => 'virtual'
        ],
        'staging' => [
            'parent_id' => null,
            'theme_path' => '',
            'theme_title' => 'Test staging theme',
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'type' => ThemeInterface::TYPE_STAGING,
            'code' => 'staging'
        ],
    ];

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
        $physicalTheme = $objectManager->create(\Magento\Framework\View\Design\ThemeInterface::class);
        $physicalTheme->setData($this->_themes['physical']);
        $physicalTheme->save();

        $this->_themes['virtual']['parent_id'] = $physicalTheme->getId();

        /** @var $virtualTheme \Magento\Framework\View\Design\ThemeInterface */
        $virtualTheme = $objectManager->create(\Magento\Framework\View\Design\ThemeInterface::class);
        $virtualTheme->setData($this->_themes['virtual']);
        $virtualTheme->save();

        $this->_themes['staging']['parent_id'] = $virtualTheme->getId();

        /** @var $stagingTheme \Magento\Framework\View\Design\ThemeInterface */
        $stagingTheme = $objectManager->create(\Magento\Framework\View\Design\ThemeInterface::class);
        $stagingTheme->setData($this->_themes['staging']);
        $stagingTheme->save();

        $this->_physicalThemeId = $physicalTheme->getId();
        $this->_virtualThemeId = $virtualTheme->getId();

        //2. run test
        /** @var $virtualTheme \Magento\Framework\View\Design\ThemeInterface */
        $virtualTheme = $objectManager->create(\Magento\Framework\View\Design\ThemeInterface::class);
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
        $this->_physicalThemeId = null;
        $this->_virtualThemeId = null;
    }
}
