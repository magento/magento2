<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\I18n\Code\Dictionary\Writer;

use Magento\Tools\I18n\Code\Dictionary\WriterInterface;
use Magento\Tools\I18n\Code\Dictionary\Phrase;

/**
 * Csv writer
 */
class Csv implements WriterInterface
{
    /**
     * File handler
     *
     * @var resource
     */
    protected $_fileHandler;

    /**
     * Writer construct
     *
     * @param string $outputFilename
     * @throws \InvalidArgumentException
     */
    public function __construct($outputFilename)
    {
        if (false === ($fileHandler = @fopen($outputFilename, 'w'))) {
            throw new \InvalidArgumentException(
                sprintf('Cannot open file for write dictionary: "%s"', $outputFilename)
            );
        }
        $this->_fileHandler = $fileHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function write(Phrase $phrase)
    {
        $fields = array($phrase->getPhrase(), $phrase->getTranslation());
        $encloseQuote = $phrase->getQuote() == Phrase::QUOTE_DOUBLE ? Phrase::QUOTE_DOUBLE : Phrase::QUOTE_SINGLE;
        $fields[0] = $this->compileString($fields[0], $encloseQuote);
        $fields[1] = $this->compileString($fields[1], $encloseQuote);
        if (($contextType = $phrase->getContextType()) && ($contextValue = $phrase->getContextValueAsString())) {
            $fields[] = $contextType;
            $fields[] = $contextValue;
        }

        fputcsv($this->_fileHandler, $fields, ',', '"');
    }

    /**
     * Compile PHP string based on quotes type it enclosed with
     *
     * @param string $string
     * @param string $encloseQuote
     * @return string
     *
     * @SuppressWarnings(PHPMD.EvalExpression)
     */
    protected function compileString($string, $encloseQuote)
    {
        $evalString = 'return ' . $encloseQuote . $string . $encloseQuote . ';';
        $result = @eval($evalString);
        return is_string($result) ? $result : $string;
    }

    /**
     * Close file handler
     *
     * @return void
     */
    public function __destructor()
    {
        fclose($this->_fileHandler);
    }
}
