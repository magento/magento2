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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Integrity_Layout_ThemeHandlesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var array|bool
     */
    protected $_codeFrontendHandles = false;

    /**
     * Check that all handles declared in a theme layout are declared in code
     *
     * @param string $handleName
     * @dataProvider designHandlesDataProvider
     */

    public function testIsDesignHandleDeclaredInCode($handleName)
    {
        $this->assertArrayHasKey(
            $handleName,
            $this->_getCodeFrontendHandles(),
            "Handle '{$handleName}' is not declared in any module.'"
        );
    }

    /**
     * @return array
     */
    public function designHandlesDataProvider()
    {
        $files = Utility_Files::init()->getLayoutFiles(array(
            'include_code' => false,
            'area' => 'frontend'
        ));

        $handles = array();
        foreach (array_keys($files) as $path) {
            $xml = simplexml_load_file($path);
            $handleNodes = $xml->xpath('/layout/*') ?: array();
            foreach ($handleNodes as $handleNode) {
                $handles[] = $handleNode->getName();
            }
        }

        $result = array();
        foreach (array_unique($handles) as $handleName) {
            $result[] = array($handleName);
        }
        return $result;
    }

    /**
     * Returns information about handles that are declared in code for frontend
     *
     * @return array
     */
    protected function _getCodeFrontendHandles()
    {
        if ($this->_codeFrontendHandles) {
            return $this->_codeFrontendHandles;
        }

        $files = Utility_Files::init()->getLayoutFiles(array(
            'include_design' => false,
            'area' => 'frontend'
        ));
        foreach (array_keys($files) as $path) {
            $xml = simplexml_load_file($path);
            $handleNodes = $xml->xpath('/layout/*') ?: array();
            foreach ($handleNodes as $handleNode) {
                $isLabel = $handleNode->xpath('label');
                if (isset($handles[$handleNode->getName()]['label_count'])) {
                    $handles[$handleNode->getName()]['label_count'] += (int)$isLabel;
                } else {
                    $handles[$handleNode->getName()]['label_count'] = (int)$isLabel;
                }
            }
        }

        $this->_codeFrontendHandles = $handles;
        return $this->_codeFrontendHandles;
    }
}
