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

        $crud = new \Magento\TestFramework\Entity($changeModel, array('change_time' => '2012-06-10 20:00:01'));
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

        return array('theme_id' => $themeModel->getId(), 'change_time' => '2013-04-10 23:34:19');
    }
}
