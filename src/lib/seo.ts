export const SITE_URL = 'https://free-bitcoin.lat';
export const SITE_NAME = 'free-bitcoin.lat';

export const COUNTRIES = [
  { slug: 'mx', locale: 'es-MX', name: 'México' },
  { slug: 'br', locale: 'pt-BR', name: 'Brasil' },
  { slug: 'ar', locale: 'es-AR', name: 'Argentina' },
  { slug: 've', locale: 'es-VE', name: 'Venezuela' },
  { slug: 'co', locale: 'es-CO', name: 'Colombia' },
  { slug: 'cl', locale: 'es-CL', name: 'Chile' },
  { slug: 'pe', locale: 'es-PE', name: 'Perú' },
  { slug: 'sv', locale: 'es-SV', name: 'El Salvador' },
  { slug: 'gt', locale: 'es-GT', name: 'Guatemala' },
  { slug: 'bo', locale: 'es-BO', name: 'Bolivia' },
  { slug: 'py', locale: 'es-PY', name: 'Paraguay' },
  { slug: 'uy', locale: 'es-UY', name: 'Uruguay' },
  { slug: 'ec', locale: 'es-EC', name: 'Ecuador' },
  { slug: 'pa', locale: 'es-PA', name: 'Panamá' },
  { slug: 'hn', locale: 'es-HN', name: 'Honduras' },
];

export interface HreflangEntry {
  lang: string;
  href: string;
}

export interface SeoMeta {
  title: string;
  description: string;
  canonical: string;
  hreflang: HreflangEntry[];
}

/**
 * Build standard SEO meta for a page.
 */
export function buildSeo(opts: {
  title: string;
  description: string;
  path: string;
  alternates?: HreflangEntry[];
}): SeoMeta {
  const canonical = `${SITE_URL}${opts.path}`;
  return {
    title: opts.title,
    description: opts.description,
    canonical,
    hreflang: opts.alternates ?? [{ lang: 'x-default', href: canonical }],
  };
}

/**
 * Build hreflang entries for country hub pages (each country → its own hub).
 */
export function countryHubHreflang(currentSlug: string, path: string): HreflangEntry[] {
  const entries: HreflangEntry[] = COUNTRIES.map((c) => ({
    lang: c.locale,
    href: `${SITE_URL}/${c.slug}/${path}`,
  }));
  entries.push({ lang: 'es-419', href: `${SITE_URL}/${currentSlug}/${path}` });
  entries.push({ lang: 'x-default', href: `${SITE_URL}/` });
  return entries;
}

/**
 * Build hreflang for a shared page type across countries (e.g. how-to-buy).
 */
export function sharedPageHreflang(countryPageMap: Record<string, string>): HreflangEntry[] {
  const entries: HreflangEntry[] = COUNTRIES
    .filter((c) => countryPageMap[c.slug])
    .map((c) => ({
      lang: c.locale,
      href: `${SITE_URL}${countryPageMap[c.slug]}`,
    }));
  const first = entries[0];
  entries.push({ lang: 'x-default', href: first?.href ?? `${SITE_URL}/` });
  return entries;
}

/**
 * Canonical URL helper – always trailing slash.
 */
export function canonical(path: string): string {
  const p = path.endsWith('/') ? path : `${path}/`;
  return `${SITE_URL}${p}`;
}
