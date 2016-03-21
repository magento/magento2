<!--
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    extension-element-prefixes="php"
    exclude-result-prefixes="xsl php">

    <xsl:output method="xml" omit-xml-declaration="yes"/>
    <xsl:variable name="schemaPath" select="'https://raw.github.com/magento/magento2/master/app/code/Mage/Core/etc/layouts.xsd'"/>

    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:copy>
    </xsl:template>

    <!-- Remove translate attribute attribute -->
    <xsl:template match="@translate[.!='true']">
        <xsl:apply-templates select="node()"/>
    </xsl:template>

    <!-- Move translate attribute to exact node -->
    <xsl:template match="*[../@translate]">
        <xsl:variable name="translate" select="../@translate"/>
        <xsl:choose>
            <xsl:when test="@name=$translate">
                <xsl:copy>
                    <xsl:attribute name="translate">true</xsl:attribute>
                    <xsl:apply-templates select="node()|@*"/>
                </xsl:copy>
            </xsl:when>
            <xsl:when test="contains($translate, ' ')">
                <xsl:call-template name="tokenize">
                    <xsl:with-param name="translate" select="$translate" />
                    <xsl:with-param name="separator" select="' '" />
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:copy>
                    <xsl:apply-templates select="node()|@*"/>
                </xsl:copy>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- Subroutine for translate it it contains separator -->
    <xsl:template name="tokenize">
        <xsl:param name="translate"/>
        <xsl:param name="separator"/>
        <xsl:variable name="first-item" select="normalize-space(substring-before(concat($translate, $separator), $separator))" />
        <xsl:choose>
            <xsl:when test="$first-item=@name">
                <xsl:copy>
                    <xsl:attribute name="translate">true</xsl:attribute>
                    <xsl:apply-templates select="node()|@*"/>
                </xsl:copy>
            </xsl:when>
            <xsl:when test="$first-item">
                <xsl:call-template name="tokenize">
                    <xsl:with-param name="translate" select="substring-after($translate, $separator)" />
                    <xsl:with-param name="separator" select="$separator" />
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:copy>
                    <xsl:apply-templates select="node()|@*"/>
                </xsl:copy>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
