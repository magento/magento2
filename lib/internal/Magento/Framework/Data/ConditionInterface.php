<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

interface ConditionInterface
{
    /**
     * Sql conditions
     */
    const EQ = 'eq';
    const NEQ = 'neq';
    const LIKE = 'like';
    const NOT_LIKE = 'nlike';
    const IN = 'in';
    const NOT_IN = 'nin';
    const IS = 'is';
    const NOT_NULL = 'notnull';
    const NULL = 'null';
    const GT = 'gt';
    const LT = 'lt';
    const GTEQ = 'gteq';
    const LTEQ = 'lteq';
    const FINSET = 'finset';
    const REGEXP = 'regexp';
    const FROM = 'from';
    const TO = 'to';
    const SEQ = 'seq';
    const SNEQ = 'sneq';
    const NTOA = 'ntoa';
}
