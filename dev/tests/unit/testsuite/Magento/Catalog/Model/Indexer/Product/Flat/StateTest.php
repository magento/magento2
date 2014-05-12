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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Product\Flat;

class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\State
     */
    protected $_model;

    public function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $indexerMock = $this->getMock('Magento\Indexer\Model\Indexer', array(), array(), '', false);
        $flatIndexerHelperMock = $this->getMock(
            'Magento\Catalog\Helper\Product\Flat\Indexer',
            array(),
            array(),
            '',
            false
        );
        $configMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_model = $this->_objectManager->getObject(
            'Magento\Catalog\Model\Indexer\Product\Flat\State',
            array(
                'scopeConfig' => $configMock,
                'flatIndexer' => $indexerMock,
                'flatIndexerHelper' => $flatIndexerHelperMock,
                false
            )
        );
    }

    public function testGetIndexer()
    {
        $this->assertInstanceOf('\Magento\Catalog\Helper\Product\Flat\Indexer', $this->_model->getFlatIndexerHelper());
    }
}
