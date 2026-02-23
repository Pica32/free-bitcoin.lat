/**
 * Client-side API helpers (used in <script> blocks).
 * Server-side fetching is not used (static site).
 */

export const API_BASE = '/api';
export const COINGECKO_FALLBACK =
  'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=usd,mxn,brl,ars,cop,clp,pen,gtq,bob,pyg,uyu,hnl,pab&include_24hr_change=true';

export const PRICE_CACHE_KEY = 'fblt_price_v1';
export const PRICE_CACHE_TTL = 5 * 60 * 1000; // 5 minutes

export interface PriceData {
  updatedAt: number;
  usd: number;
  change24h?: number;
  currencies: Record<string, number>;
}

/**
 * Fetch BTC price from our API or CoinGecko fallback.
 * Caches in localStorage.
 */
export async function fetchPrice(): Promise<PriceData | null> {
  // Check cache
  try {
    const cached = localStorage.getItem(PRICE_CACHE_KEY);
    if (cached) {
      const data: PriceData = JSON.parse(cached);
      if (Date.now() - data.updatedAt < PRICE_CACHE_TTL) {
        return data;
      }
    }
  } catch {}

  // Try our API first
  try {
    const res = await fetch(`${API_BASE}/price.php`, { signal: AbortSignal.timeout(8000) });
    if (res.ok) {
      const json = await res.json();
      const data: PriceData = {
        updatedAt: Date.now(),
        usd: json.prices?.USD?.price ?? 0,
        change24h: json.change24h?.USD,
        currencies: Object.fromEntries(
          Object.entries(json.prices ?? {}).map(([k, v]: [string, any]) => [k, v.price])
        ),
      };
      localStorage.setItem(PRICE_CACHE_KEY, JSON.stringify(data));
      return data;
    }
  } catch {}

  // Fallback: CoinGecko
  try {
    const res = await fetch(COINGECKO_FALLBACK, { signal: AbortSignal.timeout(10000) });
    if (res.ok) {
      const json = await res.json();
      const btc = json.bitcoin ?? {};
      const data: PriceData = {
        updatedAt: Date.now(),
        usd: btc.usd ?? 0,
        change24h: btc.usd_24h_change,
        currencies: {
          USD: btc.usd ?? 0,
          MXN: btc.mxn ?? 0,
          BRL: btc.brl ?? 0,
          ARS: btc.ars ?? 0,
          COP: btc.cop ?? 0,
          CLP: btc.clp ?? 0,
          PEN: btc.pen ?? 0,
          GTQ: btc.gtq ?? 0,
          BOB: btc.bob ?? 0,
          PYG: btc.pyg ?? 0,
          UYU: btc.uyu ?? 0,
          HNL: btc.hnl ?? 0,
          PAB: btc.pab ?? btc.usd ?? 0,
        },
      };
      localStorage.setItem(PRICE_CACHE_KEY, JSON.stringify(data));
      return data;
    }
  } catch {}

  return null;
}
