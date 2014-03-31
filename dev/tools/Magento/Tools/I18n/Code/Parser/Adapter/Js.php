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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\I18n\Code\Parser\Adapter;

/**
 * Js parser adapter
 */
class Js extends AbstractAdapter
{
    /**
     * {@inheritdoc}
     */
    protected function _parse()
    {
        $fileHandle = @fopen($this->_file, 'r');
        $lineNumber = 0;
        while (!feof($fileHandle)) {
            $lineNumber++;
            $fileRow = fgets($fileHandle, 4096);
            $results = array();
            preg_match_all('/mage\.__\(\s*([\'"])(.*?[^\\\])\1.*?[),]/', $fileRow, $results, PREG_SET_ORDER);
            for ($i = 0; $i < count($results); $i++) {
                if (isset($results[$i][2])) {
                    $quote = $results[$i][1];
                    $this->_addPhrase($quote . $results[$i][2] . $quote, $lineNumber);
                }
            }
        }
        fclose($fileHandle);
    }
}
