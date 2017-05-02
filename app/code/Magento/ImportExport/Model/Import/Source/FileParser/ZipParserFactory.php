<?php
/**
 * magento-2-contribution-day
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.
 *
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * @copyright  Copyright (c) 2017 EcomDev BV (http://www.ecomdev.org)
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Ivan Chepurnyi <ivan@ecomdev.org>
 */


namespace Magento\ImportExport\Model\Import\Source\FileParser;

use Magento\Framework\Filesystem;

class ZipParserFactory implements ParserFactoryInterface
{
    private $filesystem;

    public function __construct(Filesystem $filesystem, ParserFactoryInterface $factory)
    {
        $this->filesystem = $filesystem;
    }

    public function create($path, array $options = [])
    {
        if (substr($path, -4) !== '.zip') {
            throw new UnsupportedPathException($path);
        }

        throw new CorruptedFileException($path);
    }
}
