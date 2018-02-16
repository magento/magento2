<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Matcher;

/**
 * Cron expression part matcher
 *
 * @api
 */
class Year extends Generic
{
    /**
     * @var string
     */
    private $dateExpr = '%Y';

    /**
     * Year constructor.
     */
    public function __construct()
    {
        parent::__construct($this->dateExpr);
    }
}
