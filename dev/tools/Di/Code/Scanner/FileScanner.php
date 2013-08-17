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
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tools\Di\Code\Scanner;

class FileScanner implements ScannerInterface
{
    /**
     * Regular expression pattern
     *
     * @var string
     */
    protected $_pattern;

    /**
     * Get array of class names
     *
     * @param array $files
     * @return array
     */
    public function collectEntities(array $files)
    {
        $output = array();
        foreach ($files as $file) {
            $content = file_get_contents($file);
            $content = $this->_prepareContent($content);
            $matches = array();
            if (preg_match_all($this->_pattern, $content, $matches)) {
                $output = array_merge($output, $matches[1]);
            }
        }
        $output = array_unique($output);
        $output = $this->_filterEntities($output);
        return $output;
    }

    /**
     * Prepare file content
     *
     * @param string $content
     * @return string
     */
    protected function _prepareContent($content)
    {
        return $content;
    }

    /**
     * Filter found entities if needed
     *
     * @param array $output
     * @return array
     */
    protected function _filterEntities(array $output)
    {
        return $output;
    }
}
