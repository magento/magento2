<!--
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
-->
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:php="http://php.net/xsl"
    extension-element-prefixes="php"
    exclude-result-prefixes="xsl php">

    <xsl:param name="referencesFile" select="'references.xml'"/>
    <xsl:variable name="referencesDoc" select="document($referencesFile)"/>
    <xsl:variable name="blocks" select="$referencesDoc/list/item[@type = 'block']"/>
    <xsl:variable name="containers" select="$referencesDoc/list/item[@type = 'container']"/>
    <xsl:variable name="conflictNames" select="$referencesDoc/list/item[@type = 'conflictNames']"/>

    <xsl:output method="xml" omit-xml-declaration="yes"/>

    <!-- Copy nodes -->
    <xsl:template match="node()|@*">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*"/>
        </xsl:copy>
    </xsl:template>

    <xsl:template match="reference">
        <xsl:choose>
            <xsl:when test="@name=$conflictNames/@value">
                <xsl:copy>
                    <xsl:apply-templates select="node()|@*"/>
                </xsl:copy>
            </xsl:when>
            <xsl:when test="@name=$blocks/@value">
                <xsl:element name="referenceBlock">
                    <xsl:apply-templates select="node()|@*"/>
                </xsl:element>
            </xsl:when>
            <xsl:when test="@name=$containers/@value">
                <xsl:element name="referenceContainer">
                    <xsl:apply-templates select="node()|@*"/>
                </xsl:element>
            </xsl:when>
            <xsl:otherwise>
                <xsl:copy>
                    <xsl:apply-templates select="node()|@*"/>
                </xsl:copy>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
