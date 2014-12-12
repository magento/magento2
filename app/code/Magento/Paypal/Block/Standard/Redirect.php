<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
        array $data = []
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
            $form->addField($field, 'hidden', ['name' => $field, 'value' => $value]);
        }
        $idSuffix = $this->mathRandom->getUniqueHash();
        $submitButton = $this->_elementFactory->create(
            'submit',
            ['data' => ['value' => __('Click here if you are not redirected within 10 seconds.')]]
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
