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
 * @category    Mage
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page checkout agreements xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Checkout_Agreements extends Mage_Checkout_Block_Agreements
{
    /**
     * Render agreements xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        /** @var $agreementsXmlObj Mage_XmlConnect_Model_Simplexml_Element */
        $agreementsXmlObj = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element',
            array('data' => '<agreements></agreements>'));
        if ($this->getAgreements()) {
            foreach ($this->getAgreements() as $agreement) {
                $itemXmlObj = $agreementsXmlObj->addChild('item');
                $content = $agreement->getContent();
                if (!$agreement->getIsHtml()) {
                    $content = nl2br($agreementsXmlObj->escapeXml($content));
                } else {
                    $agreementsXmlObj->xmlentities($content);
                }
                $agreementText = $agreementsXmlObj->escapeXml($agreement->getCheckboxText());
                $itemXmlObj->addChild('label', $agreementText);
                $itemXmlObj->addChild('content', $content);
                $itemXmlObj->addChild('code', 'agreement[' . $agreement->getId() . ']');
                $itemXmlObj->addChild('agreement_id', $agreement->getId());
            }
        }

        return $agreementsXmlObj->asNiceXml();
    }
}
