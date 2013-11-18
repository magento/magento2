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
 * @category    Magento
 * @package     Magento_Downloadable
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for \Magento\Downloadable\Model\Observer (duplicate downloadable data)
 */
namespace Magento\Downloadable\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDataFixture Magento/Downloadable/_files/product_with_files.php
     */
    public function testDuplicateProductDownloadableProductWithFilesSuccessfullyDuplicated()
    {
        $currentProduct = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Product');
        $currentProduct->load(1); // fixture for initial product
        $currentLinks = $currentProduct->getTypeInstance($currentProduct)->getLinks($currentProduct);
        $currentSamples = $currentProduct->getTypeInstance($currentProduct)->getSamples($currentProduct);

        $newProduct = $currentProduct->duplicate();

        $newLinks = $newProduct->getTypeInstance($newProduct)->getLinks($newProduct);
        $newSamples = $newProduct->getTypeInstance($newProduct)->getSamples($newProduct);

        $this->assertEquals($currentLinks, $newLinks);
        $this->assertEquals($currentSamples, $newSamples);
    }
}
