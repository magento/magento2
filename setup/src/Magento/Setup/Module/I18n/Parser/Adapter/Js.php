<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Parser\Adapter;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;

/**
 * Js parser adapter
 */

class Js extends AbstractAdapter
{
    /**
     * @var File
     */
    private $filesystem;

    /**
     * Adapter construct
     *
     * @param File $filesystem
     */
    public function __construct(File $filesystem)
    {
        $this->filesystem = $filesystem;
    }
    /**
     * Covers
     * $.mage.__('Example text')
     */
    public const REGEX_MAGE_TRANSLATE = '/mage\.__\(\s*([\'"])(.*?[^\\\])\1.*?[),]/';

    /**
     * Covers in JS
     * $t(' Example: ')
     *
     * Covers in HTML
     * <a data-bind="attr: { title: $t('Title'), href: '#'} "></a>
     * <input type="text" data-bind="attr: { placeholder: $t('Placeholder'), title: $t('Title') }" />
     * Double quotes are not handled correctly in the `attr` binding. Move phrase to the UI component property if needed
     */
    public const REGEX_TRANSLATE_FUNCTION = '/\\$t\(\s*([\'"])(.*?[^\\\])\1.*?[),]/';

    /**
     * @inheritdoc
     *
     * @throws FileSystemException
     */
    protected function _parse()
    {
        $fileHandle = $this->filesystem->fileOpen($this->_file, 'r');
        $lineNumber = 0;
        try {
            while (($line = $this->fileReadLine($fileHandle, 0)) !== false) {
                $lineNumber++;
                $fileRow = preg_replace('/"\s+\+"|"\s+\+\s+\"|"\+\s+\"|"\+"/', '', $line);
                $fileRow = preg_replace("/'\s+\+'|'\s+\+\s+\'|'\+\s+\'|'\+'/", "", $fileRow);
                $results = [];
                $regexes = [
                    static::REGEX_MAGE_TRANSLATE,
                    static::REGEX_TRANSLATE_FUNCTION
                ];

                foreach ($regexes as $regex) {
                    preg_match_all($regex, $fileRow, $results, PREG_SET_ORDER);
                    for ($i = 0, $count = count($results); $i < $count; $i++) {
                        if (isset($results[$i][2])) {
                            $quote = $results[$i][1];
                            $this->_addPhrase($quote . $results[$i][2] . $quote, $lineNumber);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->filesystem->fileClose($fileHandle);
            throw new FileSystemException(
                new \Magento\Framework\Phrase('Stream get line failed %1', [$e->getMessage()])
            );
        }
        $this->filesystem->fileClose($fileHandle);
    }

    /**
     * Reads the line content from file pointer (with specified number of bytes from the current position).
     *
     * @param resource $resource
     * @param int $length
     * @param string $ending [optional]
     * @return string
     * @throws FileSystemException
     */
    public function fileReadLine($resource, $length, $ending = null)
    {
        try {
            // phpcs:disable
            $result = @stream_get_line($resource, $length, $ending);
            // phpcs:enable
        } catch (\Exception $e) {
            throw new FileSystemException(
                new \Magento\Framework\Phrase('Stream get line failed %1', [$e->getMessage()])
            );
        }
        return $result;
    }
}
