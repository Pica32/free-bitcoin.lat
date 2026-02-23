/**
 * validate-hreflang.mjs
 * Checks all built HTML files for hreflang tags and reports issues.
 * Run: node scripts/validate-hreflang.mjs
 */
import { readdirSync, readFileSync, statSync } from 'fs';
import { join, relative } from 'path';

const DIST = join(process.cwd(), 'dist');
const REQUIRED_LANGS = ['es-MX','pt-BR','es-AR','es-VE','es-CO','es-CL','es-PE','es-SV','es-GT','es-BO','es-PY','es-UY','es-EC','es-PA','es-HN','x-default'];

let issues = 0;
let checked = 0;

function walk(dir) {
  for (const entry of readdirSync(dir)) {
    const full = join(dir, entry);
    if (statSync(full).isDirectory()) {
      walk(full);
    } else if (entry === 'index.html') {
      checkFile(full);
    }
  }
}

function checkFile(path) {
  const html = readFileSync(path, 'utf-8');
  const rel = relative(DIST, path);

  // Find all hreflang links
  const matches = [...html.matchAll(/hreflang="([^"]+)"/g)].map(m => m[1]);

  if (matches.length === 0) {
    // Some pages may legitimately skip hreflang (forum dynamic etc.)
    return;
  }

  checked++;

  // Check for x-default
  if (!matches.includes('x-default')) {
    console.warn(`⚠️  Missing x-default: ${rel}`);
    issues++;
  }

  // Check for duplicate hreflang values
  const dupes = matches.filter((v, i, a) => a.indexOf(v) !== i);
  if (dupes.length > 0) {
    console.warn(`⚠️  Duplicate hreflang [${[...new Set(dupes)].join(', ')}]: ${rel}`);
    issues++;
  }

  // Check canonical
  const canonicals = [...html.matchAll(/rel="canonical"\s+href="([^"]+)"/g)].map(m => m[1]);
  if (canonicals.length === 0) {
    console.warn(`⚠️  Missing canonical: ${rel}`);
    issues++;
  } else if (canonicals.length > 1) {
    console.warn(`⚠️  Multiple canonicals: ${rel}`);
    issues++;
  }
}

walk(DIST);

if (issues === 0) {
  console.log(`✅ hreflang validation passed. Checked ${checked} pages.`);
} else {
  console.log(`\n❌ Found ${issues} hreflang/canonical issues across ${checked} pages.`);
  process.exit(1);
}
