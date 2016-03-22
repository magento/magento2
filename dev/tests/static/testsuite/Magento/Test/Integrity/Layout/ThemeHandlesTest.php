<?php
/**
 * Test declarations of handles in theme layout updates
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $invoker = new \Magento\Framework\App\Utility\AggregateInvoker($this);
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
        $files = \Magento\Framework\App\Utility\Files::init()->getLayoutFiles(
            ['include_code' => false, 'area' => 'frontend'],
            false
        );
        $handles = $this->_extractLayoutHandles($files);
        $result = [];
        foreach ($handles as $handleName) {
            $result[$handleName] = [$handleName];
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
            $files = \Magento\Framework\App\Utility\Files::init()->getLayoutFiles(
                ['include_design' => false, 'area' => 'frontend'],
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
        $result = [];
        foreach ($files as $filename) {
            $handleName = basename($filename, '.xml');
            $result[] = $handleName;
        }
        $result = array_unique($result);
        return $result;
    }
}
