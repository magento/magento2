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
namespace Magento\CatalogSearch\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Helper\Data
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\CatalogSearch\Helper\Data'
        );
    }

    public function testGetResultUrl()
    {
        $this->assertStringEndsWith('/catalogsearch/result/', $this->_helper->getResultUrl());

        $query = uniqid();
        $this->assertStringEndsWith("/catalogsearch/result/?q={$query}", $this->_helper->getResultUrl($query));
    }

    public function testGetAdvancedSearchUrl()
    {
        $this->assertStringEndsWith('/catalogsearch/advanced/', $this->_helper->getAdvancedSearchUrl());
    }

    public function testCheckNotesResult()
    {
        $this->assertInstanceOf('Magento\CatalogSearch\Helper\Data', $this->_helper->checkNotes());
    }

    /**
     * @magentoConfigFixture current_store catalog/search/search_type 1
     * @magentoConfigFixture current_store catalog/search/max_query_words 3
     */
    public function testCheckNotesEscapesHtmlWhenQueryIsCut()
    {
        /** @var \Magento\TestFramework\ObjectManager  $objectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\CatalogSearch\Helper\Data $catalogSearchHelper */
        $catalogSearchHelper = $this->getMock(
            'Magento\CatalogSearch\Helper\Data',
            array('getQueryText'),
            array(
                $objectManager->get('Magento\Framework\App\Helper\Context'),
                $objectManager->get('Magento\Framework\Stdlib\String'),
                $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface'),
                $objectManager->get('Magento\CatalogSearch\Model\QueryFactory'),
                $objectManager->get('Magento\Framework\Escaper'),
                $objectManager->get('Magento\Framework\Filter\FilterManager'),
                $objectManager->get('Magento\Framework\StoreManagerInterface')
            )
        );
        $catalogSearchHelper->expects(
            $this->any()
        )->method(
            'getQueryText'
        )->will(
            $this->returnValue('five <words> here <being> tested')
        );

        $catalogSearchHelper->checkNotes();

        $notes = implode($catalogSearchHelper->getNoteMessages());
        $this->assertContains('&lt;being&gt;', $notes);
        $this->assertNotContains('<being>', $notes);
    }
}
