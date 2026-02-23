import { defineConfig } from 'astro/config';
import path from 'path';

export default defineConfig({
  site: 'https://free-bitcoin.lat',
  output: 'static',
  trailingSlash: 'always',
  build: {
    format: 'directory',
  },
  vite: {
    resolve: {
      alias: {
        '@layouts': path.resolve('./src/layouts'),
        '@components': path.resolve('./src/components'),
        '@lib': path.resolve('./src/lib'),
      }
    }
  }
});
