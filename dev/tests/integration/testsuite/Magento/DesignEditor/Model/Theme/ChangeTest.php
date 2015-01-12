<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme change test
 */
namespace Magento\DesignEditor\Model\Theme;

class ChangeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test crud operations for change model using valid data
     *
     * @magentoDbIsolation enabled
     */
    public function testCrud()
    {
        /** @var $changeModel \Magento\DesignEditor\Model\Theme\Change */
        $changeModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\DesignEditor\Model\Theme\Change'
        );
        $changeModel->setData($this->_getChangeValidData());

        $crud = new \Magento\TestFramework\Entity($changeModel, ['change_time' => '2012-06-10 20:00:01']);
        $crud->testCrud();
    }

    /**
     * Get change valid data
     *
     * @return array
     */
    protected function _getChangeValidData()
    {
        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        /** @var $themeModel \Magento\Framework\View\Design\ThemeInterface */
        $theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Design\ThemeInterface'
        );
        $themeModel = $theme->getCollection()->getFirstItem();

        return ['theme_id' => $themeModel->getId(), 'change_time' => '2013-04-10 23:34:19'];
    }
}
