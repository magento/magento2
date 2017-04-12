<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Platform;

use Magento\Framework\DB\Select;

/**
 * Class Quote
 */
class Quote
{
    /**
     * @param string $identifier
     * @return string
     */
    public function quoteIdentifier($identifier)
    {
        return $this->quoteIdentifierAs($identifier);
    }

    /**
     * @param string $identifier
     * @param string|null $alias
     * @return string
     */
    public function quoteColumnAs($identifier, $alias = null)
    {
        return $this->quoteIdentifierAs($identifier, $alias);
    }

    /**
     * @param string $identifier
     * @param string|null $alias
     * @return string
     */
    public function quoteTableAs($identifier, $alias = null)
    {
        return $this->quoteIdentifierAs($identifier, $alias);
    }

    /**
     * @param string $identifier
     * @param string|null $alias
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function quoteIdentifierAs($identifier, $alias = null)
    {
        if ($identifier instanceof \Zend_Db_Expr) {
            $quoted = $identifier->__toString();
        } elseif ($identifier instanceof \Magento\Framework\DB\Select) {
            $quoted = '(' . $identifier->assemble() . ')';
        } else {
            if (is_string($identifier)) {
                $identifier = explode('.', $identifier);
            }
            if (is_array($identifier)) {
                $segments = [];
                foreach ($identifier as $segment) {
                    if ($segment instanceof \Zend_Db_Expr) {
                        $segments[] = $segment->__toString();
                    } else {
                        $segments[] = $this->replaceQuoteSymbol($segment);
                    }
                }
                if ($alias !== null && end($identifier) == $alias) {
                    $alias = null;
                }
                $quoted = implode('.', $segments);
            } else {
                $quoted = $this->replaceQuoteSymbol($identifier);
            }
        }
        if ($alias !== null) {
            $quoted .= ' ' . Select::SQL_AS . ' ' . $this->replaceQuoteSymbol($alias);
        }
        return $quoted;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function replaceQuoteSymbol($value)
    {
        $symbol = $this->getQuoteIdentifierSymbol();
        return ($symbol . str_replace("$symbol", "$symbol$symbol", $value) . $symbol);
    }

    /**
     * @return string
     */
    protected function getQuoteIdentifierSymbol()
    {
        return '`';
    }
}
