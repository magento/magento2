<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Page;

use Magento\Framework\App;

/**
 * Page title
 *
 * @api
 * @since 2.0.0
 */
class Title
{
    /**
     * Default title glue
     */
    const TITLE_GLUE = ' / ';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    private $scopeConfig;

    /**
     * @var string[]
     * @since 2.0.0
     */
    private $prependedValues = [];

    /**
     * @var string[]
     * @since 2.0.0
     */
    private $appendedValues = [];

    /**
     * @var string
     * @since 2.0.0
     */
    private $textValue;

    /**
     * @param App\Config\ScopeConfigInterface $scopeConfig
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function get()
    {
        return join(self::TITLE_GLUE, $this->build());
    }

    /**
     * Same as getTitle(), but return only first item from chunk
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getShort()
    {
        $title = $this->build();
        return reset($title);
    }

    /**
     * Same as getShort(), but return title without prefix and suffix
     * @return mixed
     * @since 2.0.0
     */
    public function getShortHeading()
    {
        $title = $this->build(false);
        return reset($title);
    }

    /**
     * @param bool $withConfigValues
     * @return array
     * @since 2.0.0
     */
    protected function build($withConfigValues = true)
    {
        return array_merge(
            $this->prependedValues,
            [$withConfigValues ? $this->addConfigValues($this->textValue) : $this->textValue],
            $this->appendedValues
        );
    }

    /**
     * @param string $title
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function append($suffix)
    {
        $this->appendedValues[] = $suffix;
    }

    /**
     * @param string $prefix
     * @return void
     * @since 2.0.0
     */
    public function prepend($prefix)
    {
        array_unshift($this->prependedValues, $prefix);
    }

    /**
     * Unset title
     *
     * @return void
     * @since 2.0.0
     */
    public function unsetValue()
    {
        $this->textValue = null;
    }
}
