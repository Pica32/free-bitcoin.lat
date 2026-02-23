<CLAUDE_CODE_MASTER_PROMPT>
  <meta>
    <domain>free-bitcoin.lat</domain>
    <date_context>Today is 2026-02-23 (Europe/Prague). All year references in titles/meta must use 2026 unless otherwise specified.</date_context>
    <nonnegotiables>
      <hosting>Wedos NoLimit shared hosting, PHP 8.x, Apache, .htaccess</hosting>
      <server_constraints>
        <no_node_on_server>true</no_node_on_server>
        <no_npm_on_server>true</no_npm_on_server>
        <no_shell_exec>true</no_shell_exec>
        <curl_available>true</curl_available>
        <curl_timeout_seconds>12</curl_timeout_seconds>
      </server_constraints>
      <frontend_output>Pure static HTML/CSS/JS in /dist, uploaded via FTP/SFTP to /www/domains/free-bitcoin.lat/</frontend_output>
      <local_build>Use Astro locally (Node 20+). Build with npm run build. Upload dist/ only.</local_build>
    </nonnegotiables>
  </meta>

  <high_level_goal>
    Build the #1 Bitcoin information resource website for Latin America across 15 countries:
    MX, BR, AR, VE, CO, CL, PE, SV, GT, BO, PY, UY, EC, PA, HN.
    Must dominate Google and Bing (traditional SEO) AND AI answers (Google AI Overviews, Bing Copilot, ChatGPT Search, Perplexity) for 3–5 years by:
    (1) locally accurate regulation/tax content with dated changelogs,
    (2) utilities that answer “what is the price / premium / cheapest remittance method”,
    (3) strict technical SEO: hreflang, canonical, JSON-LD schemas, CWV, sitemaps, IndexNow, robots + llms.txt.
  </high_level_goal>

  <ethical_and_compliance_guardrails>
    IMPORTANT: Do NOT attempt to evade AI detection or fabricate credentials/social proof.
    - Authors: use real authors if available. If the site owner later chooses pen names, include an explicit disclosure (“Nombre editorial / seudónimo”) and do not claim employment history you cannot verify.
    - Social proof: do NOT display fake “X people found this helpful”. Only show real counts derived from analytics or local storage.
    - Affiliate disclosures: must be shown wherever an affiliate link appears, in Spanish or Portuguese consistent with the page language.
    - Financial advice: include “Esto no es asesoría financiera” / “Isto não é aconselhamento financeiro” disclaimers on relevant pages.
  </ethical_and_compliance_guardrails>

  <project_setup>
    <repo_name>free-bitcoin-lat-site</repo_name>
    <local_build_stack>
      <node_version>20+</node_version>
      <package_manager>npm</package_manager>
      <ssg>Astro (static output)</ssg>
    </local_build_stack>

    <astro_config>
      - Output must be static.
      - No server-side rendering.
      - Generate clean URLs via directory indexes.
      - Set site URL to https://free-bitcoin.lat for canonical and sitemap generation.
    </astro_config>

    <deployment_workflow>
      1) Local dev: npm run dev
      2) Local build: npm run build -> dist/
      3) Upload dist/ to: /www/domains/free-bitcoin.lat/
      4) Upload PHP endpoints to: /www/domains/free-bitcoin.lat/api/
    </deployment_workflow>

    <php_api_endpoints_to_create>
      <endpoint path="/api/price.php">
        Return BTC price in USD + 15 local currencies (MXN, BRL, ARS, VES, COP, CLP, PEN, USD, SVC/USD for SV, GTQ, BOB, PYG, UYU, EUR? (optional), PAB, HNL).
        Source strategy:
        - BTC/USD from Binance spot ticker (fast + liquid).
        - Fiat FX rates from a public FX provider (primary) + fallback provider.
        - Cache JSON to /api/cache/ with TTL:
          - BTC price: 5 minutes
          - FX rates: 60 minutes
        Output: JSON { updatedAt, base: "BTC", prices: { "USD": {...}, "MXN": {...}, ... }, change24h: { ... if available } }
      </endpoint>

      <endpoint path="/api/premium.php">
        Compute BTC/USDT premium index for Argentina and Venezuela:
        - Argentina:
          official USD/ARS from BCRA reference (or a stable public source)
          crypto USD/ARS from a public “dólar cripto” source or computed from USDT ARS market data
          premium = (crypto_rate - official_rate) / official_rate
        - Venezuela:
          official USD/VES from BCV (if direct scrape is feasible) OR use an explicitly labeled “official benchmark” aggregator.
          P2P USDT/VES from p2p.army API or similar.
          premium = (p2p_usdt_rate - official_usd_rate) / official_usd_rate
        Cache TTL: 15 minutes.
        Output JSON includes inputs, sourcesUsed, updatedAt.
        If a source is unofficial, label it clearly in JSON.
      </endpoint>

      <endpoint path="/api/remittance.php">
        Serve fee tables for remittance corridors used in calculators:
        Corridors required:
          /remesas/us-mx/, /remesas/us-gt/, /remesas/us-hn/, /remesas/us-sv/, /remesas/us-co/, /remesas/us-pe/, /remesas/us-bo/
        Return JSON:
          { updatedAt, corridor, amountUSD, options: [{ method, provider, feeUSD, fxSpreadPct, totalCostUSD, speed, risk, notes }] }
        Fee tables can be hardcoded in PHP arrays and updated quarterly (use a single source of truth file).
        Cache TTL: 24 hours.
      </endpoint>

      <endpoint path="/api/forum.php">
        Anonymous forum read/write:
        - Storage: default JSON files under /api/forum_data/ with file locking (flock).
        - Optional upgrade path: SQLite if available.
        Endpoints:
          ?action=list_categories
          ?action=list_threads&category=mx
          ?action=get_thread&id=...
          POST ?action=create_thread
          POST ?action=create_reply
        Requirements:
          - No registration
          - Minimal fields: nickname (optional), title, body, createdAt, countryCategory
          - Anti-spam: honeypot + simple math captcha
          - Rate limiting: per IP using a small cache file (do not store PII beyond IP hash + timestamp)
      </endpoint>
    </php_api_endpoints_to_create>

    <php_curl_helper_pattern>
      Use this exact pattern (adapt as needed):
      <![CDATA[
function curl_get($url) {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 12,
    CURLOPT_CONNECTTIMEOUT => 6,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_USERAGENT => 'free-bitcoin.lat/1.0'
  ]);
  $body = curl_exec($ch);
  $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return [$body, $code];
}
      ]]>
    </php_curl_helper_pattern>
  </project_setup>

  <file_structure>
    Create the full project tree:

    /package.json
    /astro.config.mjs
    /tsconfig.json (minimal)
    /src/
      /pages/
        /index.astro
        /bitcoin/index.astro
        /seguridad/index.astro
        /seguridad/como-evitar-estafas/index.astro
        /seguridad/p2p-seguro/index.astro
        /seguridad/mejores-wallets/index.astro
        /glosario/index.astro
        /foro/index.astro
        /foro/[country]/index.astro
        /calculadoras/index.astro
        /calculadoras/dca/index.astro
        /calculadoras/remesas/index.astro
        /calculadoras/comisiones/index.astro
        /calculadoras/premium-btc/index.astro
        /remesas/
          /us-mx/index.astro
          /us-gt/index.astro
          /us-hn/index.astro
          /us-sv/index.astro
          /us-co/index.astro
          /us-pe/index.astro
          /us-bo/index.astro

        /mx/index.astro
        /mx/precio-bitcoin-mxn/index.astro
        /mx/como-comprar-bitcoin/index.astro
        /mx/como-vender-bitcoin/index.astro
        /mx/impuestos-bitcoin/index.astro
        /mx/regulacion-cripto/index.astro
        /mx/mejores-exchanges/index.astro
        /mx/foro/index.astro

        /br/index.astro
        /br/preco-bitcoin-brl/index.astro
        /br/como-comprar-bitcoin/index.astro
        /br/como-vender-bitcoin/index.astro
        /br/impostos-bitcoin/index.astro
        /br/regulacao-cripto/index.astro
        /br/melhores-exchanges/index.astro
        /br/forum/index.astro

        /ar/index.astro
        /ar/precio-bitcoin-ars/index.astro
        /ar/dolar-crypto/index.astro
        /ar/como-comprar-bitcoin/index.astro
        /ar/impuestos-bitcoin/index.astro
        /ar/regulacion-cripto/index.astro
        /ar/mejores-exchanges/index.astro
        /ar/foro/index.astro

        /ve/index.astro
        /ve/bitcoin-venezuela/index.astro
        /ve/p2p-seguro/index.astro
        /ve/como-comprar-bitcoin/index.astro
        /ve/impuestos-bitcoin/index.astro
        /ve/regulacion-cripto/index.astro
        /ve/mejores-exchanges/index.astro
        /ve/foro/index.astro

        /co/index.astro
        /cl/index.astro
        /pe/index.astro
        /sv/index.astro
        /gt/index.astro
        /bo/index.astro
        /py/index.astro
        /uy/index.astro
        /ec/index.astro
        /pa/index.astro
        /hn/index.astro

      /layouts/
        BaseLayout.astro
        CountryLayout.astro
        ArticleLayout.astro

      /components/
        LivePriceWidget.astro
        BTCPremiumIndex.astro
        RemittanceCalculator.astro
        DCACalculator.astro
        FeeCalculator.astro
        ExchangeComparisonTable.astro
        RegulatoryStatusBadge.astro
        AffiliateCard.astro
        ForumPreview.astro
        Breadcrumbs.astro
        FAQ.astro
        CrossLinkBitcoinchurch.astro
        SourcesList.astro

      /styles/
        tokens.css
        global.css

      /data/
        countries.json
        affiliates.json
        currencies.json
        regulators.json
        remittance_fees.json

      /lib/
        seo.ts
        schema.ts
        format.ts
        i18n.ts
        api.ts

    /public/
      /robots.txt
      /llms.txt
      /llms-full.txt
      /sitemap-index.xml (generated by script into dist)
      /sitemaps/ (generated)
      /indexnow-key.txt (placeholder; user replaces with real key)
      /favicon.svg

    /scripts/
      generate-sitemaps.mjs
      generate-llms.mjs
      validate-hreflang.mjs

    /www/
      (not in repo; this is the Wedos destination)
  </file_structure>

  <url_rules>
    - All pages must resolve without .html in the URL.
    - Build output must use directory indexes (e.g., /mx/ -> /mx/index.html).
    - Canonical must always be https://free-bitcoin.lat + current path with trailing slash.
  </url_rules>

  <htaccess_required>
    Create /public/.htaccess that will be uploaded to the web root on Wedos with:
    - RewriteEngine On
    - HTTPS redirect
    - non-www redirect (choose non-www canonical)
    - remove .html extensions if accessed
    - gzip via mod_deflate
    - cache headers:
      * static assets (css/js/fonts/images): 1 year
      * HTML: 1 hour
      * /api/*.php: 5 minutes (must still allow PHP cache TTL internally)
    - security headers:
      X-Frame-Options: DENY
      X-Content-Type-Options: nosniff
      Referrer-Policy: strict-origin-when-cross-origin
      Permissions-Policy: minimal
      Content-Security-Policy: strict but compatible with:
        * self scripts
        * allow fetch to https://api.coingecko.com AND https://free-bitcoin.lat/api/
        * allow images data: https:
  </htaccess_required>

  <design_system>
    Theme: dark professional with Bitcoin orange accents.
    Use CSS variables in tokens.css:
      --c-primary: #FF6B00
      --c-bg: #0A0A0A
      --c-surface-1: #141414
      --c-surface-2: #1C1C1C
      --c-text: #F5F5F5
      --c-text-muted: #A0A0A0
      --c-gold: #FFD700
      --c-success: #00C853
      --c-warning: #FFD600
      --c-error: #FF1744

    Typography (self-host to avoid third-party latency):
      - Display font: Montserrat (Latin-friendly; originated in Buenos Aires aesthetic)
      - Body font: Source Sans 3 (high legibility)
      - Mono: JetBrains Mono (prices/data tables)
    Self-host fonts by placing woff2 in /public/fonts/ and defining @font-face in global.css.

    Components must be accessible:
      - contrast AA
      - focus outlines
      - reduced motion
  </design_system>

  <seo_technical_implementation>
    Apply to EVERY page:
    - Title pattern: [Primary Keyword] | [Country/LatAm] | free-bitcoin.lat
    - Meta description: 150–160 chars, include keyword + geo + year 2026 + CTA
    - Hreflang:
      Provide alternates for all 15 locales + es-419 + x-default.
      For country hubs: link to each country hub.
      For page types shared across countries: link to the equivalent page in each country folder.
      For Brazil: pt-BR.
    - JSON-LD:
      * Homepage: WebSite + Organization + SiteLinksSearchBox
      * Price pages: WebPage + FAQPage + BreadcrumbList + Dataset
      * HowTo pages: HowTo + FAQPage + BreadcrumbList
      * Regulation/Tax pages: Article + FAQPage + BreadcrumbList + dateModified
      * Calculators: WebApplication + FAQPage
      * Forum pages: DiscussionForumPosting + BreadcrumbList
    - Core Web Vitals targets:
      LCP < 2.5s, INP < 200ms, CLS < 0.1
    - Sitemaps:
      /sitemap-index.xml -> global + per-country + calculators + remittances + forum
    - robots.txt:
      Allow all. Disallow /admin/. Include sitemap location.
    - IndexNow:
      Provide static key file and a script that can ping IndexNow on each build.
  </seo_technical_implementation>

  <geo_aeo_llm_optimization>
    Create llms.txt and llms-full.txt:
    - llms.txt: short, curated list of key pages + methodology + official sources list.
    - llms-full.txt: extended directory of all major pages categorized by country and page type.

    Every content page must follow this structure:
      Para 1 (40–60 words): direct answer to the page’s main query.
      Para 2: supporting context with numbers.
      Table: comparisons (exchanges, fees, premium inputs).
      FAQ: 5–8 Q&As with natural language.
      Sources: list official links (central banks, regulators, IMF, World Bank, etc).
      Updated stamp: “Última actualización: YYYY-MM-DD”.

    Voice Search:
      - Use Q phrasing as spoken Spanish per country (tú/usted/vos where relevant).
      - Answers start with the answer.
  </geo_aeo_llm_optimization>

  <affiliate_integration>
    Integrate affiliate URLs contextually. Do not spam. Add disclosure text near every affiliate CTA:

    Spanish disclosure:
      "Este es un enlace de afiliado. Si te registras, recibimos una pequeña comisión sin costo adicional para ti."

    Portuguese disclosure (Brazil):
      "Este é um link de afiliado. Se você se cadastrar, recebemos uma pequena comissão sem custo extra para você."

    Affiliate catalog: create /src/data/affiliates.json with these exact URLs:

    TIER 1:
      Binance: https://accounts.binance.com/register?ref=346436875
      Bybit: https://www.bybit.com/en/invite/?ref=NX1PKE
      KuCoin: https://www.kucoin.com/r/rf/QBSKA5LH
      Kraken: https://invite.kraken.com/JDNW/aywgmsis
      Gemini: https://exchange.gemini.com/register?referral=mzyzyvns7&type=referral
      eToro: https://www.etoro.com/?ref=46049942
      Crypto.com: https://crypto.com/app/3srf3k2sf8
      Poloniex: https://www.poloniex.com/signup?c=NDJRGRFJ
      Coinmate: https://coinmate.io?invite=VWpsaloyUlVUREp4YjNaTVVXaEdieTA1UjNnelFRPT0

    TIER 2:
      Anycoin: https://www.anycoin.cz/register?ref=d82dk0
      Trading212: https://www.trading212.com/invite/11Q8q6QkEB
      DeGiro: https://www.degiro.cz/?id=52F0C5F2
      Plus500: https://www.plus500.com/cs-cz/refer-friend
      Revolut: https://revolut.com/referral/?referral-code=kari420!JAN1-26-AR-H1
      Uphold: https://wallet.uphold.com/signup?referral=ef7b858c55
      Airtm: https://app.airtm.com/ivt/kokot5m1iwkq0

    TIER 3:
      Ledger Nano S Plus: https://shop.ledger.com/pages/ledger-nano-s-plus/?r=3771127e7ec5
      Trezor Safe 7: https://trezor.io/trezor-safe-7
      Cypherock: https://www.cypherock.com/?wt_coupon=bitcoinchurch

    TIER 4:
      BingX DAO: https://bingxdao.com/invite/PIHJRF/
      CoinEx: https://www.coinex.com/register?rc=ksvwm
      MEXC: https://promote.mexc.com/r/37KwK6My
      Bitcoin.com Wallet: https://branch.wallet.bitcoin.com/invite?code=ciLXNZoY

    TIER 6:
      Firefish: https://firefish.io?ref=satoshi713

    Placement rules:
      - Every “how to buy” page: show top 3 exchanges for that country in a table.
      - Venezuela and Argentina: highlight Airtm prominently as cash-in/out survival rail.
      - Security pages: always recommend a hardware wallet (Ledger/Trezor) with disclosure.
      - Non-KYC: allowed only as an informational mention with legal compliance warning and risk disclaimers. Do not instruct evasion.
  </affiliate_integration>

  <country_research_dataset_to_encode>
    Create /src/data/countries.json with:
    For each country: name, slug, locale, languageName, currencyCode, symbol, regulators, regulationStatus (green/yellow/red), lastRegUpdate, remittanceShareGDP_2024, topKeywordPortfolio (10 items with intent), bankingRails, culturalNotes, scamFears, recommendedContentFormats, topCompetitors, affiliatePriority (ordered list).

    Populate it with the best-known 2024–2026 status (summaries, not full law text). Use these condensed facts:

    - MX: Fintech Law baseline; Banxico Circular 4/2019 restricts FI public operations with virtual assets; AML “activos virtuales” activity; remittances ~3.6% GDP 2024.
    - BR (pt-BR): Lei 14.478/2022; Banco Central issued Resolução 520/2025 regime; Receita Federal DeCripto from IN RFB 2.291/2025; remittances ~0.2% GDP 2024.
    - AR: CNV PSAV registry RG 994/2024; RG 1058 in force (TAD workflow) May 2025; AFIP/ARCA treats gains taxable; “dólar cripto” is dominant concept; remittances relatively small.
    - VE: PDVSA uses USDT in oil sales under sanctions pressure; May 2024 mining crackdown; huge premium reality; remittances hard to measure; assume high household reliance; mark banking friction “high”.
    - CO: SFC pilot ended June 2024; Project law 510/2025 PSAV; DIAN drafts for info exchange (late 2025); remittances ~2.8% GDP 2024.
    - CL: Ley 21.521, CMF norms incl NCG 502; SII crypto tax FAQ updated 2025; remittances near 0% GDP.
    - PE: SBS Res 02648-2024 AML for PSAV; SMV warns no specific protection; remittances moderate.
    - SV: Bitcoin Law amended Jan 2025 (acceptance voluntary, IMF linked); remittances very high share.
    - GT: SIB warns not legal tender; initiative 6538 under discussion; remittances ~19% GDP.
    - BO: BCB RD 082/2024 lifted ban; transactions +530% H1 2025 vs H1 2024; USD scarcity.
    - PY: mining hub + community conflicts; central bank warnings; remittances ~2% GDP.
    - UY: Law 20.345 (2024); BCU consultation 2025; clear path -> green.
    - EC: BCE says crypto not legal tender/payment authorized; dollarized economy; remittances ~5% GDP.
    - PA: Bill 247 debated Jan 2026; SMV warnings; remittances ~0.6% GDP.
    - HN: CNBS circular 003/2024 restrictions; BCH warns not regulated; remittances ~high (mid‑20% GDP range).

    For topKeywordPortfolio, use locally phrased targets (examples; create 10 each):
      MX: "precio bitcoin hoy", "btc mxn", "comprar bitcoin spei", "comprar bitcoin oxxo", "bitso vs binance", "impuestos criptomonedas sat", "wallet bitcoin segura", "p2p bitcoin mexico", "comisiones binance retiro", "remesas bitcoin a méxico"
      BR (pt-BR): "preço do bitcoin hoje", "btc brl", "comprar bitcoin pix", "binance pix", "imposto de renda cripto", "declaração cripto receita federal", "melhores corretoras bitcoin", "carteira bitcoin segura", "taxas saque corretora", "lightning network brasil"
      AR: "precio bitcoin hoy argentina", "btc ars", "dólar cripto", "usdt ars", "comprar bitcoin mercado pago", "exchanges argentina cnv psav", "impuestos criptomonedas afip arca", "p2p seguro argentina", "blue dollar vs cripto", "mejor exchange argentina"
      VE: "usdt venezuela", "btc venezuela", "precio usdt ves", "binance p2p venezuela", "airtm venezuela", "como evitar estafas p2p", "dólar bcv vs paralelo", "wallet segura venezuela", "minería bitcoin venezuela 2024", "remesas a venezuela stablecoins"

    For remaining countries, localize currency and terms; keep intent labels.
  </country_research_dataset_to_encode>

  <forum_architecture>
    Create /foro/ hub with categories list:
      Latin America General
      mx, br, ar, ve, co, cl, pe, sv, gt, bo, py, uy, ec, pa, hn
    Each country hub page shows a ForumPreview with latest threads via /api/forum.php.
  </forum_architecture>

  <cross_link_sister_site>
    Sister site: bitcoinchurch.eu (Bitcoin education hub).
    Implement CrossLinkBitcoinchurch component:
      - Footer only and selected contextual mentions (never in main nav).
      - Spanish text: "¿Hablas español europeo? Visita bitcoinchurch.eu"
      - Must be optional and never spammy.
  </cross_link_sister_site>

  <technical_components>
    Build functional Astro components:

    1) LivePriceWidget.astro
      - Fetch from /api/price.php on same domain.
      - If API fails, fallback to CoinGecko simple price:
        https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd,mxn,brl,ars,cop,clp,pen,gtq,bob,pyg,uyu,hnl,pab&include_24hr_change=true
      - Cache in localStorage with timestamp (stale-while-revalidate 5 min).
      - Render price + 24h change + “Actualizado hace X min.”

    2) BTCPremiumIndex.astro
      - Fetch /api/premium.php
      - Render premium % with explanation tooltip.
      - Only show on AR + VE pages and /calculadoras/premium-btc/

    3) RemittanceCalculator.astro
      - Fetch /api/remittance.php?corridor=us-mx (etc)
      - Inputs: amountUSD + corridor
      - Output table: provider/method, total cost, amount received estimate, speed, risk.
      - Include Lightning and USDT options with disclaimers.

    4) DCACalculator.astro
      - Offline computation in JS
      - Chart as inline SVG

    5) FeeCalculator.astro
      - Compare: on-chain vs Lightning vs exchange withdrawal fee estimates
      - Keep it educational and label as estimates.

    6) ExchangeComparisonTable.astro
      - Data-driven from affiliates.json + country config
      - Columns: fees (approx), KYC level, payment methods, best for, affiliate CTA.

    7) RegulatoryStatusBadge.astro
      - Data-driven from countries.json
      - Color + last updated date + “Ver regulación” link.

    8) ForumPreview.astro
      - Shows latest 3 threads + link to /foro/{country}/

    9) SEO helpers:
      - seo.ts to generate title/description/canonical/hreflang arrays
      - schema.ts to generate correct JSON-LD per page type
  </technical_components>

  <content_specifications>
    You MUST include real content (not placeholders) for these priority pages.
    Write in:
      - Spanish for all except Brazil in pt-BR.
    Tone:
      - Clear, practical, non-hype.
      - Add dated “Última actualización: 2026-02-23” (or actual date per page build).
      - Include Sources section with official links.

    PRIORITY 1: Mexico (write 900–1400 words each)
      /mx/
      /mx/precio-bitcoin-mxn/
      /mx/como-comprar-bitcoin/
      /mx/impuestos-bitcoin/
      /remesas/us-mx/

    PRIORITY 2: Brazil (pt-BR, 900–1400 words each)
      /br/
      /br/preco-bitcoin-brl/
      /br/como-comprar-bitcoin/
      /br/impostos-bitcoin/

    PRIORITY 3: Argentina (900–1400 words)
      /ar/
      /ar/precio-bitcoin-ars/
      /ar/dolar-crypto/

    PRIORITY 4: Venezuela (900–1400 words)
      /ve/
      /ve/bitcoin-venezuela/
      /ve/p2p-seguro/

    PRIORITY 5: All other country hubs (CO, CL, PE, SV, GT, BO, PY, UY, EC, PA, HN)
      Create hub pages with:
        - Live price widget
        - Regulatory status summary
        - Top 3 exchanges table
        - Primary FAQ (5 Qs)
        - Forum category link
        - Cross-link to one relevant remittance corridor

    GLOBAL PAGES (write 1200–1800 words each):
      /seguridad/como-evitar-estafas/
      /seguridad/mejores-wallets/
      /calculadoras/
      /foro/

    NOTE: For the Sources section, include at least 5 official sources per country where available:
      - Central bank communications
      - Securities regulator pages
      - Tax authority guidance
      - IMF / World Bank for macro and remittances
  </content_specifications>

  <build_tasks_sequence>
    Execute in this order, without asking questions:
    1) Initialize Astro project structure and config for static output.
    2) Add styles and typography with self-hosted fonts.
    3) Implement /src/data/*.json dataset files.
    4) Build layouts and shared UI components.
    5) Implement SEO helpers and JSON-LD generators.
    6) Build calculators + widgets.
    7) Build country pages and global pages, with required content for priority pages.
    8) Build forum pages consuming /api/forum.php.
    9) Add robots.txt, llms files, sitemap generation scripts.
    10) Generate sitemaps into dist during build.
    11) Create CHECKLIST.md with the launch checklist items.
    12) Create PHP API files in /api/ with caching directories and safe permissions notes.
    13) Provide final “Upload instructions” section in README.md.
  </build_tasks_sequence>

  <launch_checklist_file>
    Create CHECKLIST.md containing:

    □ All 15 country hub pages live
    □ Mexico/Brazil/Argentina/Venezuela priority pages live with full content
    □ All calculators functional
    □ Live price widget working (API + fallback)
    □ premium index working for AR + VE
    □ hreflang implemented
    □ JSON-LD validated
    □ Core Web Vitals targets met on mobile
    □ Sitemap submitted in Google Search Console
    □ Sitemap submitted in Bing Webmaster Tools
    □ IndexNow key file placed and ping script tested
    □ llms.txt and llms-full.txt accessible
    □ All affiliate links tested
    □ Affiliate disclosures present
    □ Forum system functional and anonymous
    □ Mobile responsive (5 breakpoints)
    □ robots.txt configured
    □ Canonical tags correct
    □ Contextual cross-links to bitcoinchurch.eu present (footer + some contextual)
  </launch_checklist_file>

</CLAUDE_CODE_MASTER_PROMPT>
