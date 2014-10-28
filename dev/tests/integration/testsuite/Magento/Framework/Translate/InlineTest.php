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
namespace Magento\Framework\Translate;

class InlineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Translate\Inline
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_storeId = 'default';

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $state;

    public static function setUpBeforeClass()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        )->setDesignTheme(
            'Magento/blank'
        );
    }

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Translate\Inline'
        );
        $this->state = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Translate\Inline\StateInterface'
        );
        /* Called getConfig as workaround for setConfig bug */
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\StoreManagerInterface'
        )->getStore(
            $this->_storeId
        )->getConfig(
            'dev/translate_inline/active'
        );
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\App\Config\MutableScopeConfigInterface'
        )->setValue(
            'dev/translate_inline/active',
            true,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    public function testIsAllowed()
    {
        $this->assertTrue($this->_model->isAllowed());
        $this->assertTrue($this->_model->isAllowed($this->_storeId));
        $this->assertTrue(
            $this->_model->isAllowed(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Framework\StoreManagerInterface'
                )->getStore(
                    $this->_storeId
                )
            )
        );
        $this->state->suspend();
        $this->assertFalse($this->_model->isAllowed());
        $this->assertFalse($this->_model->isAllowed($this->_storeId));
        $this->assertFalse(
            $this->_model->isAllowed(
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Framework\StoreManagerInterface'
                )->getStore(
                    $this->_storeId
                )
            )
        );
    }

    /**
     * @param string $originalText
     * @param string $expectedText
     * @dataProvider processResponseBodyDataProvider
     */
    public function testProcessResponseBody($originalText, $expectedText)
    {
        $actualText = $originalText;
        $this->_model->processResponseBody($actualText, false);
        $this->markTestIncomplete('Bug MAGE-2494');

        $expected = new \DOMDocument();
        $expected->preserveWhiteSpace = false;
        $expected->loadHTML($expectedText);

        $actual = new \DOMDocument();
        $actual->preserveWhiteSpace = false;
        $actual->loadHTML($actualText);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function processResponseBodyDataProvider()
    {
        $originalText = file_get_contents(__DIR__ . '/_files/_inline_page_original.html');
        $expectedText = file_get_contents(__DIR__ . '/_files/_inline_page_expected.html');

        $package = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        )->getDesignTheme()->getPackageCode();
        $expectedText = str_replace('{{design_package}}', $package, $expectedText);
        return array(
            'plain text' => array('text with no translations and tags', 'text with no translations and tags'),
            'html string' => array($originalText, $expectedText),
            'html array' => array(array($originalText), array($expectedText))
        );
    }
}
