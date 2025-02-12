<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema;

use Magento\Framework\Setup\Declaration\Schema\Dto\ElementInterface;

/**
 * Element history container.
 *
 * This class holds history about element modifications.
 *
 * @api
 */
class ElementHistory
{
    /**
     * @var ElementInterface
     */
    private $new;

    /**
     * @var ElementInterface
     */
    private $old;

    /**
     * Constructor.
     *
     * @param ElementInterface $new
     * @param ElementInterface $old
     */
    public function __construct(ElementInterface $new, ?ElementInterface $old = null)
    {
        $this->new = $new;
        $this->old = $old;
    }

    /**
     * Retrieve element, that exists before we run installation.
     *
     * @return ElementInterface|null
     */
    public function getOld()
    {
        return $this->old;
    }

    /**
     * Retrieve element, that comes from configuration.
     *
     * @return ElementInterface
     */
    public function getNew()
    {
        return $this->new;
    }
}
