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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\View;

class DeployedFilesManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $area
     * @param string $themePath
     * @param string $file
     * @param string $module
     * @param string $expected
     * @dataProvider buildDeployedFilePathDataProvider
     */
    public function testBuildDeployedFilePath($area, $themePath, $file, $module, $expected)
    {
        $actual = \Magento\View\DeployedFilesManager::buildDeployedFilePath(
            $area, $themePath, $file, $module, $expected
        );
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public static function buildDeployedFilePathDataProvider()
    {
        return array(
            'no module' => array('a', 't', 'f', null, 'a/t/f'),
            'with module' => array('a', 't', 'f', 'm', 'a/t/m/f'),
        );
    }
}
