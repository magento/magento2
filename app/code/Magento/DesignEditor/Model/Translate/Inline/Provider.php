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

namespace Magento\DesignEditor\Model\Translate\Inline;

class Provider extends \Magento\Framework\Translate\Inline\Provider
{
    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $vdeInlineTranslate;

    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $inlineTranslate;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\DesignEditor\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\Translate\InlineInterface $vdeInlineTranslate
     * @param \Magento\Framework\Translate\InlineInterface $inlineTranslate
     * @param \Magento\DesignEditor\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\Translate\InlineInterface $vdeInlineTranslate,
        \Magento\Framework\Translate\InlineInterface $inlineTranslate,
        \Magento\DesignEditor\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->vdeInlineTranslate = $vdeInlineTranslate;
        $this->inlineTranslate = $inlineTranslate;
        $this->request = $request;
        $this->helper = $helper;
    }

    /**
     * Return instance of inline translate class
     *
     * @return \Magento\Framework\Translate\InlineInterface
     */
    public function get()
    {
        return $this->helper->isVdeRequest($this->request)
            ? $this->vdeInlineTranslate
            : $this->inlineTranslate;
    }
}
