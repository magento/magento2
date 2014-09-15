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
namespace Magento\TaxImportExport\Block\Adminhtml\Rate;

class ImportExportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tax\Block\Adminhtml\Rate\ImportExport
     */
    protected $_block = null;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock('Magento\TaxImportExport\Block\Adminhtml\Rate\ImportExport');
    }

    protected function tearDown()
    {
        $this->_block = null;
    }

    public function testCreateBlock()
    {
        $this->assertInstanceOf('Magento\TaxImportExport\Block\Adminhtml\Rate\ImportExport', $this->_block);
    }

    public function testFormExists()
    {
        $html = $this->_block->toHtml();

        $this->assertContains('<form id="import-form"', $html);

        $this->assertContains('<form id="export_form"', $html);
    }
}
