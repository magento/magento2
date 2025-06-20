<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Block\Bml;

use Magento\Catalog\Block as CatalogBlock;
use Magento\Paypal\Helper\Shortcut\ValidatorInterface;
use Magento\Paypal\Model\ConfigFactory;
use Magento\Paypal\Model\Config;
use Magento\Framework\App\ObjectManager;

/**
 * Class shortcut
 */
class Shortcut extends \Magento\Framework\View\Element\Template implements CatalogBlock\ShortcutInterface
{
    /**
     * Whether the block should be eventually rendered
     *
     * @var bool
     */
    protected $_shouldRender = true;

    /**
     * Payment method code
     *
     * @var string
     */
    private $_paymentMethodCode = '';

    /**
     * Shortcut alias
     *
     * @var string
     */
    private $_alias = '';

    /**
     * Start express action
     *
     * @var string
     */
    private $_startAction = '';

    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $_paymentData;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $_mathRandom;

    /**
     * Bml method code
     *
     * @var string
     */
    private $_bmlMethodCode = '';

    /**
     * Shortcut image path
     */
    const SHORTCUT_IMAGE = 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/ppcredit-logo-medium.png';

    /**
     * @var ValidatorInterface
     */
    private $_shortcutValidator;

    /**
     * @var Config
     */
    private $config;

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
     * @param ConfigFactory|null $config
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @codingStandardsIgnoreStart
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
        array $data = [],
        ?ConfigFactory $config = null
    ) {
        $this->_paymentData = $paymentData;
        $this->_mathRandom = $mathRandom;
        $this->_shortcutValidator = $shortcutValidator;
        $this->_paymentMethodCode = $paymentMethodCode;
        $this->_startAction = $startAction;
        $this->_alias = $alias;
        $this->setTemplate($shortcutTemplate);
        $this->_bmlMethodCode = $bmlMethodCode;
        $this->config = $config
            ? $config->create()
            : ObjectManager::getInstance()->get(ConfigFactory::class)->create();
        $this->config->setMethod($this->_paymentMethodCode);
        parent::__construct($context, $data);
    }
    //@codingStandardsIgnoreEnd

    /**
     * @inheritdoc
     */
    protected function _beforeToHtml()
    {
        $result = parent::_beforeToHtml();
        $isInCatalog = $this->getIsInCatalogProduct();
        if (!$this->_shortcutValidator->validate($this->_paymentMethodCode, $isInCatalog)
            || (bool)(int)$this->config->getValue('in_context')
        ) {
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
     */
    public function isOrPositionBefore()
    {
        return $this->getShowOrPosition() == CatalogBlock\ShortcutButtons::POSITION_BEFORE;
    }

    /**
     * Check is "OR" label position after shortcut
     *
     * @return bool
     */
    public function isOrPositionAfter()
    {
        return $this->getShowOrPosition() == CatalogBlock\ShortcutButtons::POSITION_AFTER;
    }

    /**
     * Get shortcut alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->_alias;
    }
}
