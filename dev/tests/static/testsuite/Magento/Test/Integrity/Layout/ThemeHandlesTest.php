<?php
/**
 * Test declarations of handles in theme layout updates
 *
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
namespace Magento\Test\Integrity\Layout;

class ThemeHandlesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array|null
     */
    protected $_baseFrontendHandles = null;

    public function testIsDesignHandleDeclaredInCode()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Check that all handles declared in a theme layout are declared in base layouts
             *
             * @param string $handleName
             */
            function ($handleName) {
                $this->assertContains(
                    $handleName,
                    $this->_getBaseFrontendHandles(),
                    "Handle '{$handleName}' is not declared in any module.'"
                );
            },
            $this->designHandlesDataProvider()
        );
    }

    /**
     * @return array
     */
    public function designHandlesDataProvider()
    {
        $files = \Magento\TestFramework\Utility\Files::init()->getLayoutFiles(
            array('include_code' => false, 'area' => 'frontend'),
            false
        );
        $handles = $this->_extractLayoutHandles($files);
        $result = array();
        foreach ($handles as $handleName) {
            $result[$handleName] = array($handleName);
        }
        return $result;
    }

    /**
     * Return layout handles that are declared in the base layouts for frontend
     *
     * @return array
     */
    protected function _getBaseFrontendHandles()
    {
        if ($this->_baseFrontendHandles === null) {
            $files = \Magento\TestFramework\Utility\Files::init()->getLayoutFiles(
                array('include_design' => false, 'area' => 'frontend'),
                false
            );
            $this->_baseFrontendHandles = $this->_extractLayoutHandles($files);
        }
        return $this->_baseFrontendHandles;
    }

    /**
     * Retrieve the list of unique layout handle names from the layout files
     *
     * @param array $files
     * @return array
     */
    protected function _extractLayoutHandles(array $files)
    {
        $result = array();
        foreach ($files as $filename) {
            $handleName = basename($filename, '.xml');
            $result[] = $handleName;
        }
        $result = array_unique($result);
        return $result;
    }
}
