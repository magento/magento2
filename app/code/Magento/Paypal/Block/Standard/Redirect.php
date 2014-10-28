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
namespace Magento\Paypal\Block\Standard;

class Redirect extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $_formFactory;

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    /**
     * @var \Magento\Paypal\Model\StandardFactory
     */
    protected $_paypalStandardFactory;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param \Magento\Paypal\Model\StandardFactory $paypalStandardFactory
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\Paypal\Model\StandardFactory $paypalStandardFactory,
        \Magento\Framework\Math\Random $mathRandom,
        array $data = array()
    ) {
        $this->_formFactory = $formFactory;
        $this->_elementFactory = $elementFactory;
        $this->_paypalStandardFactory = $paypalStandardFactory;
        $this->mathRandom = $mathRandom;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $standard = $this->_paypalStandardFactory->create();

        $form = $this->_formFactory->create();
        $form->setAction(
            $standard->getConfig()->getPaypalUrl()
        )->setId(
            'paypal_standard_checkout'
        )->setName(
            'paypal_standard_checkout'
        )->setMethod(
            'POST'
        )->setUseContainer(
            true
        );
        foreach ($standard->getStandardCheckoutFormFields() as $field => $value) {
            $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }
        $idSuffix = $this->mathRandom->getUniqueHash();
        $submitButton = $this->_elementFactory->create(
            'submit',
            array('data' => array('value' => __('Click here if you are not redirected within 10 seconds.')))
        );
        $id = "submit_to_paypal_button_{$idSuffix}";
        $submitButton->setId($id);
        $form->addElement($submitButton);
        $html = '<html><body>';
        $html .= __('You will be redirected to the PayPal website in a few seconds.');
        $html .= $form->toHtml();
        $html .= '<script type="text/javascript">document.getElementById("paypal_standard_checkout").submit();';
        $html .= '</script></body></html>';

        return $html;
    }
}
