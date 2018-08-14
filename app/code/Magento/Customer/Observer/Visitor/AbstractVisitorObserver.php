<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Observer\Visitor;

use Magento\Customer\Model\Visitor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Abstract Visitor Observer
 */
abstract class AbstractVisitorObserver implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Visitor
     */
    protected $visitor;

    /**
     * Constructor
     *
     * @param Visitor $visitor
     */
    public function __construct(Visitor $visitor)
    {
        $this->visitor = $visitor;
    }
}
