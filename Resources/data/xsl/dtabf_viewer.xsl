<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:tei="http://www.tei-c.org/ns/1.0"
  exclude-result-prefixes="tei"
  version="2.0">

  <xsl:import href="dtabf_base.xsl"/>
  <!-- TODO: switch to
  <xsl:import href="dta-tools/dta-base.xsl"/>
  <xsl:import href="dtabf_customize.xsl"/>
  -->

  <!-- copied over from dtabf_customize.xsl -->
  <!-- translate-layer -->
  <xsl:param name="lang" />
  <xsl:variable name="strings" select="document('translation.xml')/strings"/>

  <xsl:template name="translate">
   <xsl:param name="label" />
   <xsl:choose>
      <xsl:when test="$strings/string[@key=$label and @language=$lang]">
        <xsl:value-of select="$strings/string[@key=$label and @language=$lang]" />
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$label" />
      </xsl:otherwise>
   </xsl:choose>
  </xsl:template>

  <!-- from http://www.deutschestextarchiv.de/basisformat_ms.rng -->
  <xsl:template match="tei:del">
    <xsl:element name="span">
      <xsl:call-template name="applyRendition"/>
      <xsl:apply-templates/>
    </xsl:element>
  </xsl:template>

  <xsl:output method="html" doctype-system="about:legacy-compat"/>

  <xsl:template match="tei:TEI">
    <div id="{generate-id()}" class="text-layer"><xsl:apply-templates/></div>
    <script>initEntityGlossaryNote('#<xsl:value-of select="generate-id()" />')</script>
  </xsl:template>

  <!-- add support for @dir -->
  <xsl:template match='tei:p'>
    <xsl:choose>
      <xsl:when test="ancestor::tei:sp and name(preceding-sibling::*[2]) != 'p'">
        <span class="dta-in-sp"><xsl:apply-templates/></span>
      </xsl:when>
      <xsl:when test="ancestor::tei:sp and local-name(preceding-sibling::node()[1]) != 'lb' and local-name(preceding-sibling::node()[1]) != 'pb'">
        <span class="dta-in-sp"><xsl:apply-templates/></span>
      </xsl:when>
      <xsl:when test="ancestor::tei:sp and local-name(preceding-sibling::node()[1]) = 'lb'">
        <p class="dta-p-in-sp-really"><xsl:apply-templates/></p>
      </xsl:when>
      <xsl:when test="@rendition">
        <p>
          <xsl:call-template name="applyRendition"/>
          <xsl:apply-templates/>
        </p>
      </xsl:when>
      <xsl:when test="@prev">
        <p class="dta-no-indent"><xsl:apply-templates/></p>
      </xsl:when>
      <xsl:otherwise>
        <p class="dta-p">
          <xsl:if test="@dir">
            <xsl:attribute name="dir"><xsl:value-of select="@dir"/></xsl:attribute>
          </xsl:if>
          <xsl:apply-templates/>
        </p>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!-- override poems to add support for @dir -->
  <xsl:template match='tei:lg[@type="poem"]'>
    <div class="poem"><xsl:if test="@dir">
        <xsl:attribute name="dir"><xsl:value-of select="@dir"/></xsl:attribute>
      </xsl:if><xsl:apply-templates/></div>
  </xsl:template>

  <xsl:template match='tei:lg[not(@type="poem")]'>
    <div class="dta-lg"><xsl:if test="@dir">
        <xsl:attribute name="dir"><xsl:value-of select="@dir"/></xsl:attribute>
      </xsl:if><xsl:apply-templates/></div>
  </xsl:template>
  <!-- end poems -->

  <xsl:template match='tei:ref'>
    <xsl:choose>
      <xsl:when test="@target">
        <xsl:choose>
          <xsl:when test="@type = 'editorialNote'">
            <a class="hoverTooltip glossary" href="#">
              <xsl:attribute name="data-title"><xsl:value-of select="substring(@target, 2)" /></xsl:attribute>
              <xsl:apply-templates/>
            </a>
          </xsl:when>
          <xsl:otherwise>
            <a class="external">
              <xsl:attribute name="href"><xsl:value-of select="@target" /></xsl:attribute>
              <xsl:apply-templates/>
            </a>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <xsl:otherwise><xsl:apply-templates/></xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="tei:choice">
    <xsl:choose>
      <xsl:when test="./tei:reg">
        <xsl:element name="span">
          <xsl:attribute name="title"><!--Original: --><xsl:value-of select="tei:reg"/></xsl:attribute>
          <xsl:attribute name="class">dta-reg</xsl:attribute>
          <xsl:apply-templates select="tei:orig"/>
        </xsl:element>
      </xsl:when>
      <xsl:when test="./tei:abbr">
        <xsl:element name="span">
          <xsl:attribute name="title"><xsl:variable name="temp"><xsl:apply-templates select="tei:expan" mode="choice"/></xsl:variable><xsl:value-of select="normalize-space($temp)" /></xsl:attribute>
          <xsl:attribute name="class">dta-abbr</xsl:attribute>
          <xsl:apply-templates select="tei:abbr"/>
        </xsl:element>
      </xsl:when>
      <xsl:otherwise>
        <xsl:element name="span">
          <xsl:attribute name="title"><xsl:call-template name="translate">
              <xsl:with-param name="label" select="'Schreibfehler'" />
            </xsl:call-template>: <xsl:value-of select="tei:sic"/></xsl:attribute>
          <xsl:attribute name="class">dta-corr</xsl:attribute>
          <xsl:apply-templates select="tei:corr"/>
        </xsl:element>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!-- editorial notes -->
  <xsl:template match='tei:note[@type="editorial"]'>
    <xsl:choose>
      <xsl:when test="@place='foot'">
        <a class="editorial-marker img-info-sign" data-container="#image-viewer" href="#{concat($lang, generate-id())}">&#160;</a><span id="{concat($lang, generate-id())}" class="editorial foot"><xsl:apply-templates/></span>
      </xsl:when>
      <xsl:when test="@place='end'">
        <span class="fn-intext">
          <xsl:choose>
            <xsl:when test="@n">
              <!-- manually numbered -->
              <xsl:value-of select='@n'/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:number level="any" count='//tei:note[@type="editorial" and @place="end" and (text() or *) and not(@n)]' format="[I]"/>
            </xsl:otherwise>
          </xsl:choose>
        </span>
      </xsl:when>
      <xsl:otherwise>
        <!-- assume @place='inline' -->
        <span class="editorial inline"><xsl:apply-templates/></span>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!-- override to support editorial endnotes -->
  <xsl:template match='tei:pb'>
    <xsl:variable name="thisSite" select="."/>
    <xsl:if test="preceding::tei:note[@place='foot' or @place='end'][./preceding::tei:pb[. is $thisSite/preceding::tei:pb[1]]]">
      <span style="display:block; margin-left:1em">
        <xsl:for-each select="preceding::tei:note[(@place='foot' or @place='end') and string-length(@prev) > 0][./preceding::tei:pb[. is $thisSite/preceding::tei:pb[1]]]">
          <xsl:apply-templates select="." mode="footnotes"/>
        </xsl:for-each>
        <xsl:for-each select="preceding::tei:note[@place='foot' and string-length(@prev) = 0][./preceding::tei:pb[. is $thisSite/preceding::tei:pb[1]]]">
          <xsl:apply-templates select="." mode="footnotes"/>
        </xsl:for-each>
      </span>
    </xsl:if>
    <span class="dta-pb" style="padding-left:15em">|<xsl:value-of select="@facs"/><xsl:if test="@n"> : <xsl:value-of select="@n"/></xsl:if>|</span>
    <br />
  </xsl:template>

  <xsl:template match='tei:text[not(descendant::tei:text)]'>
    <xsl:apply-templates/>
    <xsl:for-each select="//tei:note[(@place='foot' or @place='end') and string-length(@prev) > 0][not(./following::tei:pb)]">
      <xsl:apply-templates select="." mode="footnotes"/>
    </xsl:for-each>
    <xsl:for-each select="//tei:note[(@place='foot' or @place='end') and string-length(@prev) = 0][not(./following::tei:pb)]">
      <xsl:apply-templates select="." mode="footnotes"/>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match='tei:note[@place="end"]' mode="footnotes">
    <xsl:if test="@type='editorial'">
    <div class="footnote" style="margin-bottom:1em">
      <xsl:choose>
        <xsl:when test="string-length(@prev)!=0 or string-length(@sameAs)!=0"></xsl:when>
        <xsl:otherwise>
          <xsl:choose>
            <xsl:when test="@n">
              <!-- manually numbered -->
              <span class="fn-sign"><xsl:value-of select='@n'/></span>
            </xsl:when>
            <xsl:otherwise>
              <xsl:number level="any" count='//tei:note[@type="editorial" and @place="end" and (text() or *) and not(@n)]' format="[I]"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:otherwise>
      </xsl:choose>
      <xsl:text> </xsl:text>
      <xsl:apply-templates/>
      <xsl:apply-templates select='tei:fw[@place="bottom"][@type="catch"]' mode="fn-catch"/>
    </div>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>
