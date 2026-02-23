/**
 * Format a number as currency.
 */
export function formatCurrency(
  amount: number,
  currency: string,
  locale: string = 'es-MX',
  decimals?: number
): string {
  const opts: Intl.NumberFormatOptions = {
    style: 'currency',
    currency,
    minimumFractionDigits: decimals ?? (amount > 1000 ? 0 : 2),
    maximumFractionDigits: decimals ?? (amount > 1000 ? 0 : 2),
  };
  try {
    return new Intl.NumberFormat(locale, opts).format(amount);
  } catch {
    return `${currency} ${amount.toLocaleString()}`;
  }
}

/**
 * Format a number with thousand separators.
 */
export function formatNumber(n: number, locale: string = 'es-MX', decimals: number = 2): string {
  return new Intl.NumberFormat(locale, {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  }).format(n);
}

/**
 * Format a percentage with sign.
 */
export function formatPct(n: number, decimals: number = 2): string {
  const sign = n > 0 ? '+' : '';
  return `${sign}${n.toFixed(decimals)}%`;
}

/**
 * Format date as YYYY-MM-DD.
 */
export function formatDate(date: Date | string): string {
  const d = typeof date === 'string' ? new Date(date) : date;
  return d.toISOString().slice(0, 10);
}

/**
 * Relative time string (Spanish).
 */
export function timeAgo(date: Date | number): string {
  const now = Date.now();
  const ts = typeof date === 'number' ? date : date.getTime();
  const diff = Math.floor((now - ts) / 1000);
  if (diff < 60) return `hace ${diff} seg`;
  if (diff < 3600) return `hace ${Math.floor(diff / 60)} min`;
  if (diff < 86400) return `hace ${Math.floor(diff / 3600)} h`;
  return `hace ${Math.floor(diff / 86400)} días`;
}
