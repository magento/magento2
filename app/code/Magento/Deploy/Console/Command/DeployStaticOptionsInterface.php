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
    const DRY_RUN_OPTION = 'dry-run';

    /**
     * Key for languages parameter
     */
    const LANGUAGE_OPTION = 'language';

    /**
     * Key for exclude languages parameter
     */
    const EXCLUDE_LANGUAGE_OPTION = 'exclude-language';

    /**
     * Key for javascript option
     */
    const JAVASCRIPT_OPTION = 'no-javascript';

    /**
     * Key for css option
     */
    const CSS_OPTION = 'no-css';

    /**
     * Key for less option
     */
    const LESS_OPTION = 'no-less';

    /**
     * Key for images option
     */
    const IMAGES_OPTION = 'no-images';

    /**
     * Key for fonts option
     */
    const FONTS_OPTION = 'no-fonts';

    /**
     * Key for misc option
     */
    const MISC_OPTION = 'no-misc';

    /**
     * Key for html option
     */
    const HTML_OPTION = 'no-html';

    /**
     * Key for html option
     */
    const HTML_MINIFY_OPTION = 'no-html-minify';

    /**
     * Key for theme option
     */
    const THEME_OPTION = 'theme';

    /**
     * Key for exclude theme option
     */
    const EXCLUDE_THEME_OPTION = 'exclude-theme';

    /**
     * Key for area option
     */
    const AREA_OPTION = 'area';

    /**
     * Key for exclude area option
     */
    const EXCLUDE_AREA_OPTION = 'exclude-area';

    /**
     * Jey for jobs option
     */
    const JOBS_AMOUNT_OPTION = 'jobs';

    /**
     * Force run of static deploy
     */
    const FORCE_RUN_OPTION = 'force';
}
