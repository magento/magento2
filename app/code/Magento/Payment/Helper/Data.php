<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Helper;

use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;
use Magento\Payment\Block\Form;
use Magento\Payment\Model\InfoInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\MethodInterface;

/**
 * Payment module base helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_PAYMENT_METHODS = 'payment';

    /**
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentConfig;

    /**
     * Layout
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Factory for payment method models
     *
     * @var \Magento\Payment\Model\Method\Factory
     */
    protected $_methodFactory;

    /**
     * App emulation model
     *
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $_appEmulation;

    /**
     * @var \Magento\Framework\App\Config\Initial
     */
    protected $_initialConfig;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param LayoutFactory $layoutFactory
     * @param \Magento\Payment\Model\Method\Factory $paymentMethodFactory
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magento\Framework\App\Config\Initial $initialConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        LayoutFactory $layoutFactory,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magento\Framework\App\Config\Initial $initialConfig
    ) {
        parent::__construct($context);
        $this->_layout = $layoutFactory->create();
        $this->_methodFactory = $paymentMethodFactory;
        $this->_appEmulation = $appEmulation;
        $this->_paymentConfig = $paymentConfig;
        $this->_initialConfig = $initialConfig;
    }

    /**
     * @param string $code
     * @return string
     */
    protected function getMethodModelConfigName($code)
    {
        return sprintf('%s/%s/model', self::XML_PATH_PAYMENT_METHODS, $code);
    }

    /**
     * Retrieve method model object
     *
     * @param string $code
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return MethodInterface
     */
    public function getMethodInstance($code)
    {
        $class = $this->scopeConfig->getValue(
            $this->getMethodModelConfigName($code),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$class) {
            throw new \UnexpectedValueException('Payment model name is not provided in config!');
        }

        return $this->_methodFactory->create($class);
    }

    /**
     * Get and sort available payment methods for specified or current store
     *
     * @param null|string|bool|int $store
     * @param Quote|null $quote
     * @return AbstractMethod[]
     * @deprecated 100.1.3
     * @see \Magento\Payment\Api\PaymentMethodListInterface
     */
    public function getStoreMethods($store = null, $quote = null)
    {
        $res = [];
        $methods = $this->getPaymentMethods();

        foreach (array_keys($methods) as $code) {
            $model = $this->scopeConfig->getValue(
                $this->getMethodModelConfigName($code),
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
            if (!$model) {
                continue;
            }

            /** @var AbstractMethod $methodInstance */
            $methodInstance = $this->_methodFactory->create($model);
            $methodInstance->setStore($store);
            if (!$methodInstance->isAvailable($quote)) {
                /* if the payment method cannot be used at this time */
                continue;
            }
            $res[] = $methodInstance;
        }

        @uasort(
            $res,
            function (MethodInterface $a, MethodInterface $b) {
                if ((int)$a->getConfigData('sort_order') < (int)$b->getConfigData('sort_order')) {
                    return -1;
                }

                if ((int)$a->getConfigData('sort_order') > (int)$b->getConfigData('sort_order')) {
                    return 1;
                }

                return 0;
            }
        );

        return $res;
    }

    /**
     * Retrieve payment method form html
     *
     * @param MethodInterface $method
     * @param LayoutInterface $layout
     * @return Form
     */
    public function getMethodFormBlock(MethodInterface $method, LayoutInterface $layout)
    {
        $block = $layout->createBlock($method->getFormBlockType(), $method->getCode());
        $block->setMethod($method);
        return $block;
    }

    /**
     * Retrieve payment information block
     *
     * @param InfoInterface $info
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @return Template
     */
    public function getInfoBlock(InfoInterface $info, LayoutInterface $layout = null)
    {
        $layout = $layout ?: $this->_layout;
        $blockType = $info->getMethodInstance()->getInfoBlockType();
        $block = $layout->createBlock($blockType);
        $block->setInfo($info);
        return $block;
    }

    /**
     * Render payment information block
     *
     * @param InfoInterface $info
     * @param int $storeId
     * @return string
     * @throws \Exception
     */
    public function getInfoBlockHtml(InfoInterface $info, $storeId)
    {
        $this->_appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = $this->getInfoBlock($info);
            $paymentBlock->setArea(\Magento\Framework\App\Area::AREA_FRONTEND)
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()
                ->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();
        } catch (\Exception $exception) {
            $this->_appEmulation->stopEnvironmentEmulation();
            throw $exception;
        }

        $this->_appEmulation->stopEnvironmentEmulation();

        return $paymentBlockHtml;
    }

    /**
     * Retrieve all payment methods
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        return $this->_initialConfig->getData('default')[self::XML_PATH_PAYMENT_METHODS];
    }

    /**
     * Retrieve all payment methods list as an array
     *
     * Possible output:
     * 1) assoc array as <code> => <title>
     * 2) array of array('label' => <title>, 'value' => <code>)
     * 3) array of array(
     *                 array('value' => <code>, 'label' => <title>),
     *                 array('value' => array(
     *                     'value' => array(array(<code1> => <title1>, <code2> =>...),
     *                     'label' => <group name>
     *                 )),
     *                 array('value' => <code>, 'label' => <title>),
     *                 ...
     *             )
     *
     * @param bool $sorted
     * @param bool $asLabelValue
     * @param bool $withGroups
     * @param Store|null $store
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getPaymentMethodList($sorted = true, $asLabelValue = false, $withGroups = false, $store = null)
    {
        $methods = [];
        $groups = [];
        $groupRelations = [];

        foreach ($this->getPaymentMethods() as $code => $data) {
            if (isset($data['title'])) {
                $methods[$code] = $data['title'];
            } else {
                $methods[$code] = $this->getMethodInstance($code)->getConfigData('title', $store);
            }
            if ($asLabelValue && $withGroups && isset($data['group'])) {
                $groupRelations[$code] = $data['group'];
            }
        }
        if ($asLabelValue && $withGroups) {
            $groups = $this->_paymentConfig->getGroups();
            foreach ($groups as $code => $title) {
                $methods[$code] = $title;
            }
        }
        if ($sorted) {
            asort($methods);
        }
        if ($asLabelValue) {
            $labelValues = [];
            foreach ($methods as $code => $title) {
                $labelValues[$code] = [];
            }
            foreach ($methods as $code => $title) {
                if (isset($groups[$code])) {
                    $labelValues[$code]['label'] = $title;
                    $labelValues[$code]['value'] = null;
                } elseif (isset($groupRelations[$code])) {
                    unset($labelValues[$code]);
                    $labelValues[$groupRelations[$code]]['value'][$code] = ['value' => $code, 'label' => $title];
                } else {
                    $labelValues[$code] = ['value' => $code, 'label' => $title];
                }
            }
            return $labelValues;
        }

        return $methods;
    }

    /**
     * Returns value of Zero Subtotal Checkout / Enabled
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function isZeroSubTotal($store = null)
    {
        return $this->scopeConfig->getValue(
            \Magento\Payment\Model\Method\Free::XML_PATH_PAYMENT_FREE_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Returns value of Zero Subtotal Checkout / New Order Status
     *
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getZeroSubTotalOrderStatus($store = null)
    {
        return $this->scopeConfig->getValue(
            \Magento\Payment\Model\Method\Free::XML_PATH_PAYMENT_FREE_ORDER_STATUS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Returns value of Zero Subtotal Checkout / Automatically Invoice All Items
     *
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getZeroSubTotalPaymentAutomaticInvoice($store = null)
    {
        return $this->scopeConfig->getValue(
            \Magento\Payment\Model\Method\Free::XML_PATH_PAYMENT_FREE_PAYMENT_ACTION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
