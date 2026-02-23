# free-bitcoin.lat

The #1 Bitcoin information resource for Latin America — 15 countries, real content, live prices, remittance calculators and regulatory updates.

## Local Development

```bash
npm install
npm run dev       # Start dev server at localhost:4321
npm run build     # Build static site → dist/
npm run preview   # Preview built site
```

## Project Structure

```
free-bitcoin-lat-site/
├── src/
│   ├── pages/          # All routes (Astro static pages)
│   │   ├── index.astro       # Homepage
│   │   ├── mx/               # Mexico pages (es-MX)
│   │   ├── br/               # Brazil pages (pt-BR)
│   │   ├── ar/               # Argentina pages
│   │   ├── ve/               # Venezuela pages
│   │   ├── co/ cl/ pe/ ...   # Other 11 country hubs
│   │   ├── calculadoras/     # Calculator pages
│   │   ├── remesas/          # Remittance corridor pages
│   │   ├── seguridad/        # Security guides
│   │   ├── foro/             # Forum pages
│   │   ├── bitcoin/          # What is Bitcoin guide
│   │   └── glosario/         # Crypto glossary
│   ├── layouts/        # BaseLayout, CountryLayout, ArticleLayout
│   ├── components/     # Reusable Astro components
│   ├── styles/         # tokens.css + global.css
│   ├── data/           # JSON data files
│   └── lib/            # TypeScript helpers (seo, schema, format, i18n)
├── public/
│   ├── fonts/          # Self-hosted Montserrat, Source Sans 3, JetBrains Mono
│   ├── favicon.svg
│   ├── robots.txt
│   ├── llms.txt
│   ├── llms-full.txt
│   ├── .htaccess       # Apache config for Wedos
│   └── indexnow-key.txt  # Replace with real IndexNow key
├── api/                # PHP endpoints (NOT in dist — deploy separately)
│   ├── price.php       # BTC price in 15 currencies (Binance + FX + cache)
│   ├── premium.php     # BTC premium for AR + VE
│   ├── remittance.php  # Remittance fee tables (7 corridors)
│   ├── forum.php       # Anonymous forum API (JSON file storage)
│   └── cache/          # Auto-created; must be writable on server
├── scripts/
│   ├── generate-sitemaps.mjs   # Runs after astro build
│   ├── generate-llms.mjs       # Copies llms files to dist
│   └── validate-hreflang.mjs   # Validates hreflang in built HTML
└── CHECKLIST.md        # Pre-launch checklist
```

## Upload Instructions (Wedos Deployment)

### Step 1 — Build locally
```bash
npm run build
# Runs: astro build + generate-sitemaps.mjs + generate-llms.mjs
```

### Step 2 — Upload dist/ to Wedos web root
Upload **everything inside** `dist/` to:
```
/www/domains/free-bitcoin.lat/
```

Do NOT upload `node_modules/`, `src/`, `scripts/`, or root `package.json`.

The `.htaccess` from `public/` is included in `dist/` automatically.

### Step 3 — Upload PHP API files separately
Upload everything inside `api/` to:
```
/www/domains/free-bitcoin.lat/api/
```

Create these writable directories on the server (via FTP or SSH):
```
chmod 755 /www/domains/free-bitcoin.lat/api/cache/
chmod 755 /www/domains/free-bitcoin.lat/api/forum_data/
```

### Step 4 — Configure IndexNow
1. Generate a key at https://www.bing.com/indexnow
2. Replace placeholder in `public/indexnow-key.txt` with your real key
3. Also create `[your-key].txt` in `public/` containing just the key
4. Rebuild, re-upload, verify at `https://free-bitcoin.lat/[your-key].txt`

### Step 5 — Verify deployment
```bash
curl https://free-bitcoin.lat/api/price.php
curl https://free-bitcoin.lat/robots.txt
curl https://free-bitcoin.lat/llms.txt
curl https://free-bitcoin.lat/sitemap-index.xml
```

## Updating Content

| What to update | Where |
|---|---|
| Remittance fee tables | `/api/remittance.php` — `$FEE_TABLES` array, update quarterly |
| Country regulatory data | `/src/data/regulators.json` |
| Affiliate links | `/src/data/affiliates.json` |
| Exchange comparisons | `/src/pages/[country]/mejores-exchanges/` |
| FX rate fallbacks | `/api/price.php` — static fallback array |
| Venezuela/Argentina BCV/BCRA fallback | `/api/premium.php` |

## Font Files Note

The fonts in `public/fonts/` are TTF files with `.woff2` extensions (downloaded from Google Fonts). The `@font-face` declarations use `format('truetype')` so browsers parse them correctly. For production optimization (~30% size reduction), convert to actual woff2 using:
```bash
# Install woff2 tools (Linux/Mac)
woff2_compress public/fonts/Montserrat-Regular.woff2
# Then rename the output .woff2 file properly
```

## Architecture

- **Static HTML** — Astro SSG, no server-side rendering
- **No Node.js on server** — only PHP 8.x + Apache (Wedos NoLimit)
- **Prices** — fetched client-side from `/api/price.php` with CoinGecko fallback
- **Forum** — PHP JSON file storage with `flock()`; optional SQLite upgrade
- **Fonts** — self-hosted for CWV / no third-party latency
- **CSS** — pure CSS custom properties, zero framework
- **JS** — vanilla only, no React/Vue/Alpine/jQuery
