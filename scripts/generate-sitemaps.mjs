/**
 * generate-sitemaps.mjs
 * Generates sitemap XML files into dist/sitemaps/ after Astro build.
 * Run: node scripts/generate-sitemaps.mjs
 */

import { writeFileSync, mkdirSync, existsSync } from 'fs';
import { join } from 'path';

const SITE = 'https://free-bitcoin.lat';
const DIST = join(process.cwd(), 'dist');
const SITEMAPS_DIR = join(DIST, 'sitemaps');

if (!existsSync(DIST)) {
  console.error('dist/ not found. Run npm run build first (without sitemap step).');
  process.exit(1);
}

mkdirSync(SITEMAPS_DIR, { recursive: true });

const now = new Date().toISOString().slice(0, 10);

function url(loc, priority = '0.7', changefreq = 'weekly') {
  return `  <url>
    <loc>${SITE}${loc}</loc>
    <lastmod>${now}</lastmod>
    <changefreq>${changefreq}</changefreq>
    <priority>${priority}</priority>
  </url>`;
}

function sitemap(name, urls) {
  const content = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${urls.join('\n')}
</urlset>`;
  const path = join(SITEMAPS_DIR, `sitemap-${name}.xml`);
  writeFileSync(path, content, 'utf-8');
  console.log(`✓ sitemap-${name}.xml (${urls.length} URLs)`);
  return path;
}

// --- Global pages ---
const globalUrls = [
  url('/', '1.0', 'daily'),
  url('/bitcoin/', '0.9', 'monthly'),
  url('/glosario/', '0.7', 'monthly'),
  url('/seguridad/', '0.8', 'monthly'),
  url('/seguridad/como-evitar-estafas/', '0.8', 'monthly'),
  url('/seguridad/mejores-wallets/', '0.8', 'monthly'),
  url('/seguridad/p2p-seguro/', '0.7', 'monthly'),
  url('/foro/', '0.7', 'daily'),
];
sitemap('global', globalUrls);

// --- Calculators ---
const calcUrls = [
  url('/calculadoras/', '0.9', 'weekly'),
  url('/calculadoras/dca/', '0.8', 'monthly'),
  url('/calculadoras/remesas/', '0.9', 'weekly'),
  url('/calculadoras/premium-btc/', '0.9', 'daily'),
  url('/calculadoras/comisiones/', '0.7', 'monthly'),
];
sitemap('calculadoras', calcUrls);

// --- Remesas ---
const remesaUrls = [
  url('/remesas/us-mx/', '0.9', 'weekly'),
  url('/remesas/us-gt/', '0.8', 'weekly'),
  url('/remesas/us-hn/', '0.8', 'weekly'),
  url('/remesas/us-sv/', '0.8', 'weekly'),
  url('/remesas/us-co/', '0.8', 'weekly'),
  url('/remesas/us-pe/', '0.8', 'weekly'),
  url('/remesas/us-bo/', '0.7', 'weekly'),
];
sitemap('remesas', remesaUrls);

// --- Mexico ---
const mxUrls = [
  url('/mx/', '1.0', 'daily'),
  url('/mx/precio-bitcoin-mxn/', '1.0', 'daily'),
  url('/mx/como-comprar-bitcoin/', '0.9', 'monthly'),
  url('/mx/como-vender-bitcoin/', '0.8', 'monthly'),
  url('/mx/impuestos-bitcoin/', '0.9', 'weekly'),
  url('/mx/regulacion-cripto/', '0.8', 'weekly'),
  url('/mx/mejores-exchanges/', '0.8', 'weekly'),
  url('/mx/foro/', '0.6', 'daily'),
];
sitemap('mx', mxUrls);

// --- Brazil ---
const brUrls = [
  url('/br/', '1.0', 'daily'),
  url('/br/preco-bitcoin-brl/', '1.0', 'daily'),
  url('/br/como-comprar-bitcoin/', '0.9', 'monthly'),
  url('/br/como-vender-bitcoin/', '0.8', 'monthly'),
  url('/br/impostos-bitcoin/', '0.9', 'weekly'),
  url('/br/regulacao-cripto/', '0.8', 'weekly'),
  url('/br/melhores-exchanges/', '0.8', 'weekly'),
  url('/br/forum/', '0.6', 'daily'),
];
sitemap('br', brUrls);

// --- Argentina ---
const arUrls = [
  url('/ar/', '1.0', 'daily'),
  url('/ar/precio-bitcoin-ars/', '1.0', 'daily'),
  url('/ar/dolar-crypto/', '1.0', 'daily'),
  url('/ar/como-comprar-bitcoin/', '0.9', 'monthly'),
  url('/ar/impuestos-bitcoin/', '0.8', 'weekly'),
  url('/ar/regulacion-cripto/', '0.8', 'weekly'),
  url('/ar/mejores-exchanges/', '0.8', 'weekly'),
  url('/ar/foro/', '0.6', 'daily'),
];
sitemap('ar', arUrls);

// --- Venezuela ---
const veUrls = [
  url('/ve/', '1.0', 'daily'),
  url('/ve/bitcoin-venezuela/', '0.9', 'monthly'),
  url('/ve/p2p-seguro/', '0.9', 'weekly'),
  url('/ve/como-comprar-bitcoin/', '0.9', 'monthly'),
  url('/ve/regulacion-cripto/', '0.7', 'weekly'),
  url('/ve/mejores-exchanges/', '0.8', 'weekly'),
  url('/ve/foro/', '0.6', 'daily'),
];
sitemap('ve', veUrls);

// --- Other countries ---
const otherCountries = ['co','cl','pe','sv','gt','bo','py','uy','ec','pa','hn'];
const otherUrls = otherCountries.flatMap(c => [
  url(`/${c}/`, '0.8', 'weekly'),
  url(`/${c}/foro/`, '0.5', 'daily'),
]);
sitemap('other', otherUrls);

// --- Sitemap index ---
const sitemapNames = ['global','calculadoras','remesas','mx','br','ar','ve','other'];
const indexContent = `<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
${sitemapNames.map(name => `  <sitemap>
    <loc>${SITE}/sitemaps/sitemap-${name}.xml</loc>
    <lastmod>${now}</lastmod>
  </sitemap>`).join('\n')}
</sitemapindex>`;

writeFileSync(join(DIST, 'sitemap-index.xml'), indexContent, 'utf-8');
console.log(`✓ sitemap-index.xml (${sitemapNames.length} sitemaps)`);
console.log('\n✅ Sitemap generation complete.');
