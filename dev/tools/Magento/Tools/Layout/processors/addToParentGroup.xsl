<!--
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:php="http://php.net/xsl"
    xsl:extension-element-prefixes="php"
    exclude-result-prefixes="xsl php"
    >

    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*" />
        </xsl:copy>
    </xsl:template>

    <xsl:template match="block[action[@method='addToParentGroup']]">
        <xsl:copy>
            <xsl:copy-of select="@*" />
            <xsl:attribute name="group">
                <xsl:value-of select="action[@method='addToParentGroup']/*[1]" />
            </xsl:attribute>
            <xsl:apply-templates select="*" />
        </xsl:copy>
    </xsl:template>

    <!-- Delete action -->
    <xsl:template match="action[@method='addToParentGroup']">
    </xsl:template>

</xsl:stylesheet>
