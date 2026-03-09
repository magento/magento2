<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Ui\Component\Form\Element\DataType\Media;

/**
 * Basic configuration for OpenDialogUrl
 */
class OpenDialogUrl
{
    private const DEFAULT_OPEN_DIALOG_URL = 'cms/wysiwyg_images/index';

    /**
     * @var string
     */
    private $openDialogUrl;

    /**
     * @param string $url
     */
    public function __construct(?string $url = null)
    {
        $this->openDialogUrl = $url ?? self::DEFAULT_OPEN_DIALOG_URL;
    }

    /**
     * Returns open dialog url for media browser
     *
     * @return string
     */
    public function get(): string
    {
        return $this->openDialogUrl;
    }
}
