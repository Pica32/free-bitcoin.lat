# free-bitcoin.lat — Launch Checklist

## Content & Pages
- [ ] All 15 country hub pages live (MX, BR, AR, VE, CO, CL, PE, SV, GT, BO, PY, UY, EC, PA, HN)
- [ ] Mexico priority pages live with full content (/mx/, /mx/precio-bitcoin-mxn/, /mx/como-comprar-bitcoin/, /mx/impuestos-bitcoin/)
- [ ] Brazil priority pages live in pt-BR (/br/, /br/preco-bitcoin-brl/, /br/como-comprar-bitcoin/, /br/impostos-bitcoin/)
- [ ] Argentina priority pages live (/ar/, /ar/precio-bitcoin-ars/, /ar/dolar-crypto/)
- [ ] Venezuela priority pages live (/ve/, /ve/bitcoin-venezuela/, /ve/p2p-seguro/)
- [ ] Remittance corridor pages live (us-mx, us-gt, us-hn, us-sv, us-co, us-pe, us-bo)
- [ ] Global pages live (/bitcoin/, /glosario/, /seguridad/, /foro/, /calculadoras/)
- [ ] "Última actualización: YYYY-MM-DD" on every article page

## Technical & SEO
- [ ] hreflang implemented on all pages (all 15 locales + es-419 + x-default)
- [ ] Canonical tags correct (https://free-bitcoin.lat + trailing slash on every page)
- [ ] JSON-LD validated (use https://validator.schema.org)
- [ ] Title pattern: [Keyword] | [Country] | free-bitcoin.lat
- [ ] Meta descriptions 150–160 chars on all pages
- [ ] robots.txt accessible at https://free-bitcoin.lat/robots.txt
- [ ] llms.txt accessible at https://free-bitcoin.lat/llms.txt
- [ ] llms-full.txt accessible at https://free-bitcoin.lat/llms-full.txt

## Core Web Vitals (measure with PageSpeed Insights)
- [ ] LCP < 2.5s on mobile
- [ ] INP < 200ms
- [ ] CLS < 0.1
- [ ] No layout shift from fonts (font-display: swap configured)

## Sitemaps & Indexing
- [ ] sitemap-index.xml accessible at https://free-bitcoin.lat/sitemap-index.xml
- [ ] Sitemap submitted in Google Search Console
- [ ] Sitemap submitted in Bing Webmaster Tools
- [ ] IndexNow key file placed (/indexnow-key.txt) with REAL key (replace placeholder)
- [ ] IndexNow ping tested after first deployment

## Functionality
- [ ] Live price widget working on all country pages (API + CoinGecko fallback)
- [ ] Price widget shows correct currency for each country
- [ ] localStorage cache working (check browser DevTools)
- [ ] BTC Premium index working for Argentina (/calculadoras/premium-btc/)
- [ ] BTC Premium index working for Venezuela
- [ ] All calculators functional: DCA, Remesas, Comisiones, Premium
- [ ] Remittance Calculator fetches /api/remittance.php correctly
- [ ] Forum system functional and anonymous (/foro/)
- [ ] Forum captcha working (math captcha + honeypot)
- [ ] Forum rate limiting working
- [ ] Forum posts persist correctly

## PHP API (Wedos server)
- [ ] /api/price.php returns valid JSON (test: curl https://free-bitcoin.lat/api/price.php)
- [ ] /api/premium.php?country=ar returns valid JSON
- [ ] /api/premium.php?country=ve returns valid JSON
- [ ] /api/remittance.php?corridor=us-mx returns valid JSON
- [ ] /api/forum.php?action=list_categories returns valid JSON
- [ ] /api/cache/ directory created and writable (chmod 755)
- [ ] /api/forum_data/ directory created and writable
- [ ] /api/forum_data/[category]/ subdirectories created
- [ ] PHP error display OFF on server (php_flag display_errors Off)

## Design & Accessibility
- [ ] Mobile responsive across 5 breakpoints (320px, 480px, 768px, 1024px, 1280px)
- [ ] Dark theme consistent across all pages
- [ ] Color contrast AA on all text (check with axe DevTools)
- [ ] Focus outlines visible for keyboard navigation
- [ ] Reduced motion respected (@media prefers-reduced-motion)
- [ ] Mobile navigation menu working (hamburger toggle)

## Affiliate & Compliance
- [ ] All affiliate links tested and working
- [ ] Affiliate disclosure present wherever affiliate links appear (Spanish/Portuguese)
- [ ] "Esto no es asesoría financiera" disclaimer on every page
- [ ] Plus500 CFD risk disclaimer where applicable
- [ ] No fake social proof ("X users found this helpful" type)
- [ ] Author disclosure if pen names used

## Cross-links
- [ ] CrossLinkBitcoinchurch in footer (NOT in main nav)
- [ ] Contextual cross-links to /bitcoin/, /seguridad/, remesas corridors

## Server Configuration (Wedos)
- [ ] .htaccess uploaded to web root
- [ ] HTTPS redirect working
- [ ] www → non-www redirect working
- [ ] Gzip compression active (check with curl -H "Accept-Encoding: gzip")
- [ ] Security headers present (check with securityheaders.com)
- [ ] Static asset cache headers set (1 year)
- [ ] HTML cache headers set (1 hour)

## Post-Launch
- [ ] Google Search Console verified
- [ ] Bing Webmaster Tools verified
- [ ] Google Analytics or privacy-friendly analytics configured
- [ ] Monitor /api/price.php for uptime (simple cron or external monitor)
- [ ] Test IndexNow ping script
- [ ] Test all 7 remittance corridor pages
- [ ] Soft-launch social media announcement
