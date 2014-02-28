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
 * @category    Magento
 * @package     Magento_Paypal
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Block\Adminhtml\System\Config\Fieldset;

/**
 * Renderer for service JavaScript code that disables corresponding paypal methods on page load
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Store
    extends \Magento\Backend\Block\Template
    implements \Magento\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * Path to template file
     *
     * @var string
     */
    protected $_template = 'Magento_Paypal::system/config/fieldset/store.phtml';

    /**
     * @var \Magento\App\ConfigInterface
     */
    protected $_coreConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\App\ConfigInterface $coreConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\App\ConfigInterface $coreConfig,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_coreConfig = $coreConfig;
    }

    /**
     * Render service JavaScript code
     *
     * @param \Magento\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_storeManager->isSingleStoreMode() ? '' : $this->toHtml();
        return $html;
    }

    /**
     * Returns list of disabled (in the Default or the Website Scope) paypal methods
     *
     * @return array
     */
    protected function getPaypalDisabledMethods()
    {
        // Assoc array that contains info about paypal methods (their IDs and corresponding Config Paths)
        $methods = array(
            'express'   => 'payment/paypal_express/active',
            'wps'       => 'payment/paypal_standard/active',
            'wpp'       => 'payment/paypal_direct/active',
            'wpppe'     => 'payment/payflow_direct/active',
            'payflowpro'  => 'payment/payflowpro/active',
            'expresspe' => 'payment/payflow_express/active'
        );
        // Retrieve a code of the current website
        $website = $this->getRequest()->getParam('website');
        $disabledMethods = array();
        foreach ($methods as $methodId => $methodPath) {
            $isEnabled = (int)$this->_coreConfig->getValue($methodPath, 'website', $website);
            if ($isEnabled === 0) {
                $disabledMethods[$methodId] = $isEnabled;
            }
        }
        return $disabledMethods;
    }
}
