/**
 * generate-llms.mjs
 * Copies llms.txt and llms-full.txt to dist/ (they're already in public/ but this ensures they're fresh).
 */
import { copyFileSync, existsSync } from 'fs';
import { join } from 'path';

const DIST = join(process.cwd(), 'dist');

if (!existsSync(DIST)) {
  console.error('dist/ not found. Run npm run build first.');
  process.exit(1);
}

try {
  copyFileSync(join(process.cwd(), 'public', 'llms.txt'), join(DIST, 'llms.txt'));
  copyFileSync(join(process.cwd(), 'public', 'llms-full.txt'), join(DIST, 'llms-full.txt'));
  console.log('✓ llms.txt and llms-full.txt copied to dist/');
} catch (e) {
  console.warn('Warning: Could not copy llms files:', e.message);
}
