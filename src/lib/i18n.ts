export type Lang = 'es' | 'pt-BR';

export const UI = {
  es: {
    nav: {
      bitcoin: 'Bitcoin',
      calculators: 'Calculadoras',
      security: 'Seguridad',
      forum: 'Foro',
      remittances: 'Remesas',
    },
    footer: {
      disclaimer: 'Esto no es asesoría financiera.',
      affiliate: 'Este es un enlace de afiliado. Si te registras, recibimos una pequeña comisión sin costo adicional para ti.',
    },
    price: {
      updated: 'Actualizado',
      loading: 'Cargando precio...',
      change24h: 'Cambio 24h',
    },
    regulatory: {
      green: 'Regulado ✓',
      yellow: 'En regulación',
      red: 'Sin marco legal',
      lastUpdate: 'Última actualización',
    },
    common: {
      sources: 'Fuentes oficiales',
      lastUpdated: 'Última actualización',
      readMore: 'Leer más',
      learnMore: 'Más información',
      calculate: 'Calcular',
      result: 'Resultado',
      notFinancialAdvice: 'Esto no es asesoría financiera. Bitcoin conlleva riesgos. Solo invierte lo que puedas permitirte perder.',
    },
  },
  'pt-BR': {
    nav: {
      bitcoin: 'Bitcoin',
      calculators: 'Calculadoras',
      security: 'Segurança',
      forum: 'Fórum',
      remittances: 'Remessas',
    },
    footer: {
      disclaimer: 'Isto não é aconselhamento financeiro.',
      affiliate: 'Este é um link de afiliado. Se você se cadastrar, recebemos uma pequena comissão sem custo extra para você.',
    },
    price: {
      updated: 'Atualizado',
      loading: 'Carregando preço...',
      change24h: 'Variação 24h',
    },
    regulatory: {
      green: 'Regulamentado ✓',
      yellow: 'Em regulamentação',
      red: 'Sem marco legal',
      lastUpdate: 'Última atualização',
    },
    common: {
      sources: 'Fontes oficiais',
      lastUpdated: 'Última atualização',
      readMore: 'Ler mais',
      learnMore: 'Mais informações',
      calculate: 'Calcular',
      result: 'Resultado',
      notFinancialAdvice: 'Isto não é aconselhamento financeiro. Bitcoin envolve riscos. Invista apenas o que você pode perder.',
    },
  },
} as const;

export function t(lang: Lang, key: string): string {
  const parts = key.split('.');
  let obj: unknown = UI[lang] || UI['es'];
  for (const part of parts) {
    if (obj && typeof obj === 'object' && part in (obj as object)) {
      obj = (obj as Record<string, unknown>)[part];
    } else {
      return key;
    }
  }
  return typeof obj === 'string' ? obj : key;
}

export function langFromSlug(slug: string): Lang {
  return slug === 'br' ? 'pt-BR' : 'es';
}
