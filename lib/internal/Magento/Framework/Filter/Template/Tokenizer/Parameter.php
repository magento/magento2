<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\Template\Tokenizer;

/**
 * Template constructions parameters tokenizer
 */
class Parameter extends \Magento\Framework\Filter\Template\Tokenizer\AbstractTokenizer
{
    /**
     * Tokenize string and return getted parameters
     *
     * @return array
     */
    public function tokenize()
    {
        $parameters = [];
        $parameterName = '';
        while ($this->next()) {
            if ($this->isWhiteSpace()) {
                continue;
            } elseif ($this->char() != '=') {
                $parameterName .= $this->char();
            } else {
                $parameters[$parameterName] = $this->getValue();
                $parameterName = '';
            }
        }
        return $parameters;
    }

    /**
     * Get string value in parameters through tokenize
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getValue()
    {
        $this->next();
        $value = '';
        if ($this->isWhiteSpace()) {
            return $value;
        }
        $quoteStart = $this->char() == "'" || $this->char() == '"';

        if ($quoteStart) {
            $breakSymbol = $this->char();
        } else {
            $breakSymbol = false;
            $value .= $this->char();
        }

        while ($this->next()) {
            if (!$breakSymbol && $this->isWhiteSpace()) {
                break;
            } elseif ($breakSymbol && $this->char() == $breakSymbol) {
                break;
            } elseif ($this->char() == '\\') {
                $this->next();
                if ($this->char() != '\\') {
                    $value .= '\\';
                }
                $value .= $this->char();
            } else {
                $value .= $this->char();
            }
        }
        return $value;
    }
}
