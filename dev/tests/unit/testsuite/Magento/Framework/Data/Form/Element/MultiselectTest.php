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
namespace Magento\Framework\Data\Form\Element;

class MultiselectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Multiselect
     */
    protected $_model;

    protected function setUp()
    {
        $testHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $testHelper->getObject('Magento\Framework\Data\Form\Element\Editablemultiselect');
        $this->_model->setForm(new \Magento\Framework\Object());
    }

    /**
     * Verify that hidden input is present in multiselect
     *
     * @covers \Magento\Framework\Data\Form\Element\Multiselect::getElementHtml
     */
    public function testHiddenFieldPresentInMultiSelect()
    {
        $this->_model->setDisabled(true);
        $this->_model->setCanBeEmpty(true);
        $elementHtml = $this->_model->getElementHtml();
        $this->assertContains('<input type="hidden"', $elementHtml);
    }

    /**
     * Verify that js element is added
     */
    public function testGetAfterElementJs()
    {
        $this->_model->setAfterElementJs('<script language="text/javascript">var website = "website1";</script>');
        $elementHtml = $this->_model->getAfterElementJs();
        $this->assertContains('var website = "website1";', $elementHtml);
    }
}
