<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Block\Bml;

use Magento\Catalog\Block as CatalogBlock;
use Magento\Paypal\Helper\Shortcut\ValidatorInterface;

/**
 * Class \Magento\Paypal\Block\Bml\Shortcut
 *
 * @since 2.0.0
 */
class Shortcut extends \Magento\Framework\View\Element\Template implements CatalogBlock\ShortcutInterface
{
    /**
     * Whether the block should be eventually rendered
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_shouldRender = true;

    /**
     * Payment method code
     *
     * @var string
     * @since 2.0.0
     */
    private $_paymentMethodCode = '';

    /**
     * Shortcut alias
     *
     * @var string
     * @since 2.0.0
     */
    private $_alias = '';

    /**
     * Start express action
     *
     * @var string
     * @since 2.0.0
     */
    private $_startAction = '';

    /**
     * @var \Magento\Payment\Helper\Data
     * @since 2.0.0
     */
    private $_paymentData;

    /**
     * @var \Magento\Framework\Math\Random
     * @since 2.0.0
     */
    private $_mathRandom;

    /**
     * Bml method code
     *
     * @var string
     * @since 2.0.0
     */
    private $_bmlMethodCode = '';

    /**
     * Shortcut image path
     */
    const SHORTCUT_IMAGE = 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/ppcredit-logo-medium.png';

    /**
     * @var ValidatorInterface
     * @since 2.0.0
     */
    private $_shortcutValidator;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param ValidatorInterface $shortcutValidator
     * @param string $paymentMethodCode
     * @param string $startAction
     * @param string $alias
     * @param string $bmlMethodCode
     * @param string $shortcutTemplate
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Math\Random $mathRandom,
        ValidatorInterface $shortcutValidator,
        $paymentMethodCode,
        $startAction,
        $alias,
        $bmlMethodCode,
        $shortcutTemplate,
        array $data = []
    ) {
        $this->_paymentData = $paymentData;
        $this->_mathRandom = $mathRandom;
        $this->_shortcutValidator = $shortcutValidator;

        $this->_paymentMethodCode = $paymentMethodCode;
        $this->_startAction = $startAction;
        $this->_alias = $alias;
        $this->setTemplate($shortcutTemplate);
        $this->_bmlMethodCode = $bmlMethodCode;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\View\Element\AbstractBlock
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $result = parent::_beforeToHtml();
        $isInCatalog = $this->getIsInCatalogProduct();
        if (!$this->_shortcutValidator->validate($this->_paymentMethodCode, $isInCatalog)) {
            $this->_shouldRender = false;
            return $result;
        }

        /** @var \Magento\Paypal\Model\Express $method */
        $method = $this->_paymentData->getMethodInstance($this->_bmlMethodCode);
        if (!$method->isAvailable()) {
            $this->_shouldRender = false;
            return $result;
        }

        $this->setShortcutHtmlId($this->_mathRandom->getUniqueHash('ec_shortcut_bml_'))
            ->setCheckoutUrl($this->getUrl($this->_startAction))
            ->setImageUrl(self::SHORTCUT_IMAGE)
            ->setAdditionalLinkImage(
                [
                    'href' => 'https://www.securecheckout.billmelater.com/paycapture-content/'
                    . 'fetch?hash=AU826TU8&content=/bmlweb/ppwpsiw.html',
                    'src' => 'https://www.paypalobjects.com/webstatic/en_US/btn/btn_bml_text.png',
                ]
            );

        return $result;
    }

    /**
     * Render the block if needed
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        if (!$this->_shouldRender) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * Check is "OR" label position before shortcut
     *
     * @return bool
     * @since 2.0.0
     */
    public function isOrPositionBefore()
    {
        return $this->getShowOrPosition() == CatalogBlock\ShortcutButtons::POSITION_BEFORE;
    }

    /**
     * Check is "OR" label position after shortcut
     *
     * @return bool
     * @since 2.0.0
     */
    public function isOrPositionAfter()
    {
        return $this->getShowOrPosition() == CatalogBlock\ShortcutButtons::POSITION_AFTER;
    }

    /**
     * Get shortcut alias
     *
     * @return string
     * @since 2.0.0
     */
    public function getAlias()
    {
        return $this->_alias;
    }
}
