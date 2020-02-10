<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sniffs\Html;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Sniffing improper HTML bindings.
 */
class HtmlBindingSniff implements Sniff
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        return [T_INLINE_HTML];
    }

    /**
     * Load HTML document to validate.
     *
     * @param int $stackPointer
     * @param File $file
     * @return \DOMDocument|null
     */
    private function loadHtmlDocument(int $stackPointer, File $file): ?\DOMDocument
    {
        if ($stackPointer === 0) {
            $html = $file->getTokensAsString($stackPointer, count($file->getTokens()));
            $dom = new \DOMDocument();
            try {
                // phpcs:disable Generic.PHP.NoSilencedErrors
                @$dom->loadHTML($html);
                return $dom;
            } catch (\Throwable $exception) {
                return null;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     *
     * Find HTML data bindings and check variables used.
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        if (!$dom = $this->loadHtmlDocument($stackPtr, $phpcsFile)) {
            return;
        }

        /** @var string[] $htmlBindings */
        $htmlBindings = [];
        $domXpath = new \DOMXPath($dom);
        $dataBindAttributes = $domXpath->query('//@*[name() = "data-bind"]');
        foreach ($dataBindAttributes as $dataBindAttribute) {
            $knockoutBinding = $dataBindAttribute->nodeValue;
            preg_match('/^(.+\s*?)?html\s*?\:(.+)/ims', $knockoutBinding, $htmlBindingStart);
            if ($htmlBindingStart) {
                $htmlBinding = trim(preg_replace('/\,[a-z0-9\_\s]+\:.+/ims', '', $htmlBindingStart[2]));
                $htmlBindings[] = $htmlBinding;
            }
        }
        $htmlAttributes = $domXpath->query('//@*[name() = "html"]');
        foreach ($htmlAttributes as $htmlAttribute) {
            $magentoBinding = $htmlAttribute->nodeValue;
            $htmlBindings[] = trim($magentoBinding);
        }
        foreach ($htmlBindings as $htmlBinding) {
            if (!preg_match('/^[0-9\\\'\"]/ims', $htmlBinding)
                && !preg_match('/UnsanitizedHtml(\(.*?\))*?$/', $htmlBinding)
            ) {
                $phpcsFile->addError(
                    'Variables/functions used for HTML binding must have UnsanitizedHtml suffix'
                    . ' - "' . $htmlBinding . '" doesn\'t,' . PHP_EOL
                    . 'consider using text binding if the value is supposed to be text',
                    null,
                    'UIComponentTemplate.KnockoutBinding.HtmlSuffix'
                );
            }
        }
    }
}
