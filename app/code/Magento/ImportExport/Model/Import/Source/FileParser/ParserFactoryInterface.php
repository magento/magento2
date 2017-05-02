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

/**
 * File parser factory instance
 *
 */
interface ParserFactoryInterface
{
    /**
     * Creates a file parser instance for specified file
     *
     * @param string $path
     * @param array $options
     *
     * @return ParserInterface
     */
    public function create($path, array $options = []);
}
