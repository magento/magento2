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
 * Run a twig syntax checker
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Twig;

class TwigExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider listAllTwigFiles
     */
    public function testTwigTemplateValid($file, $runTest = true)
    {
        if (!$runTest) {
            return;
        }
        exec('php ' . __DIR__ . "/_files/twig-lint.phar lint $file", $output, $returnVar);
        $this->assertEquals(
            0, $returnVar,
            "File $file could not be validated by twig-lint.  The output is : \n" . implode("\n", $output)
        );
    }

    public function listAllTwigFiles()
    {
        $testData = \Magento\TestFramework\Utility\Files::composeDataSets(
            \Magento\TestFramework\Utility\Files::init()->getTwigFiles());
        if (empty($testData)) {
            $testData[] = array('Dummy data for when no twig files exist.', false);
        }
        return $testData;
    }
}
