<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Deploy\Console\Command;

interface DeployStaticOptionsInterface
{
    /**
     * Key for dry-run option
     */
    const DRY_RUN = 'dry-run';

    /**
     * Key for languages parameter
     */
    const LANGUAGE = 'language';

    /**
     * Key for exclude languages parameter
     */
    const EXCLUDE_LANGUAGE = 'exclude-language';

    /**
     * Key for javascript option
     */
    const NO_JAVASCRIPT = 'no-javascript';

    /**
     * Key for css option
     */
    const NO_CSS = 'no-css';

    /**
     * Key for less option
     */
    const NO_LESS = 'no-less';

    /**
     * Key for images option
     */
    const NO_IMAGES = 'no-images';

    /**
     * Key for fonts option
     */
    const NO_FONTS = 'no-fonts';

    /**
     * Key for misc option
     */
    const NO_MISC = 'no-misc';

    /**
     * Key for html option
     */
    const NO_HTML = 'no-html';

    /**
     * Key for html option
     */
    const NO_HTML_MINIFY = 'no-html-minify';

    /**
     * Key for theme option
     */
    const THEME = 'theme';

    /**
     * Key for exclude theme option
     */
    const EXCLUDE_THEME = 'exclude-theme';

    /**
     * Key for area option
     */
    const AREA = 'area';

    /**
     * Key for exclude area option
     */
    const EXCLUDE_AREA = 'exclude-area';

    /**
     * Jey for jobs option
     */
    const JOBS_AMOUNT = 'jobs';

    /**
     * Force run of static deploy
     */
    const FORCE_RUN = 'force';

    /**
     * Symlink locale if it not customized
     */
    const SYMLINK_LOCALE = 'symlink-locale';
}
