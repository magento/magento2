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
namespace Magento\Translation\Model;

class InlineParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Translation\Model\Inline\Parser
     */
    protected $_inlineParser;

    /** @var string */
    protected $_storeId = 'default';

    protected function setUp()
    {
        /** @var $inline \Magento\Framework\Translate\Inline */
        $inline = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Translate\Inline');
        $this->_inlineParser = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Translation\Model\Inline\Parser',
            array('translateInline' => $inline)
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

    /**
     * @dataProvider processAjaxPostDataProvider
     */
    public function testProcessAjaxPost($originalText, $translatedText, $isPerStore = null)
    {
        $inputArray = array(array('original' => $originalText, 'custom' => $translatedText));
        if ($isPerStore !== null) {
            $inputArray[0]['perstore'] = $isPerStore;
        }
        $this->_inlineParser->processAjaxPost($inputArray);

        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Translation\Model\String'
        );
        $model->load($originalText);
        try {
            $this->assertEquals($translatedText, $model->getTranslate());
            $model->delete();
        } catch (\Exception $e) {
            $model->delete();
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
                ->get('Magento\Framework\Logger')
                ->logException($e);
        }
    }

    /**
     * @return array
     */
    public function processAjaxPostDataProvider()
    {
        return array(
            array('original text 1', 'translated text 1'),
            array('original text 2', 'translated text 2', true)
        );
    }

    public function testSetGetIsJson()
    {
        $isJsonProperty = new \ReflectionProperty(get_class($this->_inlineParser), '_isJson');
        $isJsonProperty->setAccessible(true);

        $this->assertFalse($isJsonProperty->getValue($this->_inlineParser));

        $setIsJsonMethod = new \ReflectionMethod($this->_inlineParser, 'setIsJson');
        $setIsJsonMethod->setAccessible(true);
        $setIsJsonMethod->invoke($this->_inlineParser, true);

        $this->assertTrue($isJsonProperty->getValue($this->_inlineParser));
    }
}
