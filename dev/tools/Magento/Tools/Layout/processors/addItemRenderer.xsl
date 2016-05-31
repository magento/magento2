<!--
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    xsl:extension-element-prefixes="php"
    exclude-result-prefixes="xsl php"
>
    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="action[@method='addItemRender']">
        <block class="{*[2]}" as="{*[1]}" template="{*[3]}"></block>
    </xsl:template>

    <xsl:template match="action[@method='addRowItemRender']">
        <block class="{*[2]}" as="row-{*[1]}" template="{*[3]}"></block>
    </xsl:template>
</xsl:stylesheet>
