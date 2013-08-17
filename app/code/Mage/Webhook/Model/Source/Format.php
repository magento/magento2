<?php
/**
 * The list of available formats
 *
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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Model_Source_Format
{
    /** @var Mage_Core_Model_Translate  */
    private $_translator;

    /** @var string[] $_formats */
    private $_formats;

    /**
     * Cache of options
     *
     * @var null|array
     */
    protected $_options = null;

    /**
     * @param Mage_Core_Model_Translate $translator
     * @param string[] $formats
     */
    public function __construct(array $formats, Mage_Core_Model_Translate $translator)
    {
        $this->_translator = $translator;
        $this->_formats = $formats;
    }

    /**
     * Get available formats
     *
     * @return string[]
     */
    public function toOptionArray()
    {
        return $this->_formats;
    }

    /**
     * Return non-empty formats for use by a form
     *
     * @return array
     */
    public function getFormatsForForm()
    {
        $elements = array();
        foreach ($this->_formats as $formatName => $format) {
            $elements[] = array(
                'label' => $this->_translator->translate(array($format)),
                'value' => $formatName,
            );
        }

        return $elements;
    }
}
