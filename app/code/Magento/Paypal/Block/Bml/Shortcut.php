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
 
namespace Magento\Paypal\Block\Bml;

use Magento\Catalog\Block as CatalogBlock;
use Magento\Paypal\Helper\Shortcut\ValidatorInterface;

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
    const SHORTCUT_IMAGE = 'https://www.paypalobjects.com/webstatic/en_US/btn/btn_bml_SM.png';

    /**
     * @var ValidatorInterface
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
     * @param array $data
     */
    public function __construct
    (
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Math\Random $mathRandom,
        ValidatorInterface $shortcutValidator,
        $paymentMethodCode,
        $startAction,
        $alias,
        $bmlMethodCode,
        array $data = array()
    ) {
        $this->_paymentData = $paymentData;
        $this->_mathRandom = $mathRandom;
        $this->_shortcutValidator = $shortcutValidator;

        $this->_paymentMethodCode = $paymentMethodCode;
        $this->_startAction = $startAction;
        $this->_alias = $alias;
        $this->_bmlMethodCode = $bmlMethodCode;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\View\Element\AbstractBlock
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
        if (!$method || !$method->isAvailable()) {
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
                    'src' => 'https://www.paypalobjects.com/webstatic/en_US/btn/btn_bml_text.png'
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
