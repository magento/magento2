<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\Template;

/**
 * Class \Magento\Framework\Filter\Template\Simple
 *
 * @since 2.0.0
 */
class Simple extends \Magento\Framework\DataObject implements \Zend_Filter_Interface
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_startTag = '{{';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_endTag = '}}';

    /**
     * Set tags
     *
     * @param string $start
     * @param string $end
     * @return $this
     * @since 2.0.0
     */
    public function setTags($start, $end)
    {
        $this->_startTag = $start;
        $this->_endTag = $end;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $value
     * @return string
     * @since 2.0.0
     */
    public function filter($value)
    {
        $callback = function ($matches) {
            return $this->getData($matches[1]);
        };
        $expression = '#' . preg_quote($this->_startTag, '#') . '(.*?)' . preg_quote($this->_endTag, '#') . '#';
        return preg_replace_callback($expression, $callback, $value);
    }
}
