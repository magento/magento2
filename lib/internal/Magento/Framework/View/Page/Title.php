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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\View\Page;

use Magento\Framework\App;
use Magento\Framework\View;

/**
 * Page title
 */
class Title
{
    /**
     * Default title glue
     */
    const TITLE_GLUE = ' / ';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /** @var string[] */
    private $prependedValues = [];

    /** @var string[] */
    private $appendedValues = [];

    /**
     * @var string
     */
    private $textValue;

    /**
     * @param App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Set page title
     *
     * @param string $title
     * @return $this
     */
    public function set($title)
    {
        $this->textValue = $title;
        return $this;
    }

    /**
     * Retrieve title element text (encoded)
     *
     * @return string
     */
    public function get()
    {
        return join(self::TITLE_GLUE, $this->build());
    }

    /**
     * Same as getTitle(), but return only first item from chunk
     *
     * @return mixed
     */
    public function getShort()
    {
        $title = $this->build();
        return reset($title);
    }

    /**
     * @return array
     */
    protected function build()
    {
        return array_merge($this->prependedValues, [$this->addConfigValues($this->textValue)], $this->appendedValues);
    }

    /**
     * @param string $title
     * @return string
     */
    protected function addConfigValues($title)
    {
        $preparedTitle = $this->scopeConfig->getValue(
            'design/head/title_prefix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) . ' ' . $title . ' ' . $this->scopeConfig->getValue(
            'design/head/title_suffix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return trim($preparedTitle);
    }

    /**
     * Retrieve default title text
     *
     * @return string
     */
    public function getDefault()
    {
        $defaultTitle = $this->scopeConfig->getValue(
            'design/head/default_title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $this->addConfigValues($defaultTitle);
    }

    /**
     * @param string $suffix
     * @return void
     */
    public function append($suffix)
    {
        $this->appendedValues[] = $suffix;
    }

    /**
     * @param string $prefix
     * @return void
     */
    public function prepend($prefix)
    {
        array_unshift($this->prependedValues, $prefix);
    }

    /**
     * Unset title
     *
     * @return void
     */
    public function unsetValue()
    {
        $this->textValue = null;
    }
}
