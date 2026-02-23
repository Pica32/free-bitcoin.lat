import { readFileSync, writeFileSync, readdirSync, statSync } from 'fs';
import { join, extname } from 'path';

const LAYOUTS = new Set(['BaseLayout.astro', 'ArticleLayout.astro', 'CountryLayout.astro']);

const COMPONENT_NAMES = [
  'Breadcrumbs', 'BTCPremiumIndex', 'DCACalculator', 'ExchangeComparisonTable',
  'FAQ', 'FeeCalculator', 'ForumPreview', 'LivePriceWidget',
  'RegulatoryStatusBadge', 'RemittanceCalculator', 'SourcesList',
  'AffiliateCard', 'CrossLinkBitcoinchurch', 'RegulatoryStatusBadge',
];

const LIB_NAMES = ['schema', 'seo', 'format', 'i18n', 'api'];

function getAllAstroFiles(dir, results = []) {
  for (const entry of readdirSync(dir)) {
    const full = join(dir, entry);
    if (statSync(full).isDirectory()) {
      getAllAstroFiles(full, results);
    } else if (extname(entry) === '.astro') {
      results.push(full);
    }
  }
  return results;
}

function fixImports(content) {
  let changed = false;
  let result = content;

  // Fix layout imports: from "/BaseLayout.astro" -> from "@layouts/BaseLayout.astro"
  for (const layout of LAYOUTS) {
    const before = `from "/${layout}"`;
    const after = `from "@layouts/${layout}"`;
    if (result.includes(before)) {
      result = result.split(before).join(after);
      changed = true;
    }
    // single quotes
    const beforeSq = `from '/${layout}'`;
    const afterSq = `from '@layouts/${layout}'`;
    if (result.includes(beforeSq)) {
      result = result.split(beforeSq).join(afterSq);
      changed = true;
    }
  }

  // Fix component imports: from "/FAQ.astro" -> from "@components/FAQ.astro"
  for (const comp of COMPONENT_NAMES) {
    const before = `from "/${comp}.astro"`;
    const after = `from "@components/${comp}.astro"`;
    if (result.includes(before)) {
      result = result.split(before).join(after);
      changed = true;
    }
    const beforeSq = `from '/${comp}.astro'`;
    const afterSq = `from '@components/${comp}.astro'`;
    if (result.includes(beforeSq)) {
      result = result.split(beforeSq).join(afterSq);
      changed = true;
    }
  }

  // Fix lib imports: from "/schema" -> from "@lib/schema"
  for (const lib of LIB_NAMES) {
    // Match exact: from "/schema" (with quote immediately after)
    const beforeDq = `from "/${lib}"`;
    const afterDq = `from "@lib/${lib}"`;
    if (result.includes(beforeDq)) {
      result = result.split(beforeDq).join(afterDq);
      changed = true;
    }
    const beforeSq = `from '/${lib}'`;
    const afterSq = `from '@lib/${lib}'`;
    if (result.includes(beforeSq)) {
      result = result.split(beforeSq).join(afterSq);
      changed = true;
    }
  }

  return { result, changed };
}

const pagesDir = join(process.cwd(), 'src', 'pages');
const files = getAllAstroFiles(pagesDir);

let totalChanged = 0;
for (const file of files) {
  const content = readFileSync(file, 'utf8');
  const { result, changed } = fixImports(content);
  if (changed) {
    writeFileSync(file, result, 'utf8');
    console.log('Fixed:', file.replace(process.cwd(), ''));
    totalChanged++;
  }
}

console.log(`\nDone. Fixed ${totalChanged} of ${files.length} files.`);
