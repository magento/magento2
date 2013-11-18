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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Resource\Product\Flat;

class IndexerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Resource\Product\Flat\Indexer
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Resource\Product\Flat\Indexer');
    }

    public function testGetAttributeCodes()
    {
        $actualResult = $this->_model->getAttributeCodes();
        $this->assertContains('name', $actualResult);
        $this->assertContains('price', $actualResult);
        $nameAttributeId = array_search('name', $actualResult);
        $priceAttributeId = array_search('price', $actualResult);
        $this->assertGreaterThan(0, $nameAttributeId, 'Id of the attribute "name" must be valid');
        $this->assertGreaterThan(0, $priceAttributeId, 'Id of the attribute "name" must be valid');
        $this->assertNotEquals($nameAttributeId, $priceAttributeId, 'Attribute ids must be different');
    }
}
