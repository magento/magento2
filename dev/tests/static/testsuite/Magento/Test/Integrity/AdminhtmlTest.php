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
 * @category    tests
 * @package     static
 * @subpackage  Integrity
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Integrity;
use \Magento\TestFramework\Utility\Files;

class AdminhtmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider decouplingDataProvider
     *
     * @param $file
     */
    public function testAdminhtmlDecoupling($file)
    {
        $blackList = $this->_getDecouplingBlackList();
        $blackList = array_map(
            function ($element) {
                $element = str_replace('/', DIRECTORY_SEPARATOR, $element);
                return preg_quote($element, '/');
            },
            $blackList
        );
        $this->assertRegExp('/(' . implode('|', $blackList) . ')/', $file);
    }

    /**
     * @return array
     */
    public function decouplingDataProvider()
    {
        $pathToModule = Files::init()->getPathToSource()
            . DIRECTORY_SEPARATOR . 'app'
            . DIRECTORY_SEPARATOR . 'code'
            . DIRECTORY_SEPARATOR . 'Magento'
            . DIRECTORY_SEPARATOR . 'Adminhtml'
        ;

        $result = glob(
            $pathToModule . DIRECTORY_SEPARATOR . '{Block,Controller,Helper,Model}'. DIRECTORY_SEPARATOR . '*',
            GLOB_BRACE | GLOB_NOSORT
        );
        // append views
        $result = array_merge($result, glob(
            $pathToModule . DIRECTORY_SEPARATOR . 'view[^layout]'
                . DIRECTORY_SEPARATOR . 'adminhtml' . DIRECTORY_SEPARATOR . '*',
            GLOB_BRACE | GLOB_NOSORT
        ));
        // append layouts
        $result = array_merge($result, glob(
            $pathToModule . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'adminhtml'
                . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . '*',
            GLOB_BRACE | GLOB_NOSORT
        ));

        return Files::composeDataSets($result);
    }

    /**
     * @return array
     */
    protected function _getDecouplingBlackList()
    {
        return require __DIR__ . DIRECTORY_SEPARATOR
            . '_files' . DIRECTORY_SEPARATOR
            . 'blacklist' . DIRECTORY_SEPARATOR
            . 'adminhtml_decoupling.php';
    }
}
