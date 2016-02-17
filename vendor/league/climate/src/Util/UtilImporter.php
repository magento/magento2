<?php

namespace League\CLImate\Util;

trait UtilImporter
{
    /**
     * An instance of the UtilFactory
     *
     * @var \League\CLImate\Util\UtilFactory $util
     */
    protected $util;

    /**
     * Sets the $util property
     */
    public function util(UtilFactory $util)
    {
        $this->util = $util;
    }

}
