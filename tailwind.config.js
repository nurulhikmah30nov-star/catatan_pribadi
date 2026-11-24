/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        canvas: '#F8FAFC',
        surface: '#FFFFFF',
        hero: '#0F172A',
        main: '#0F172A',
        muted: '#475569',
        brand: {
          hero: '#0F172A',
          mint: { bg: '#ECFDF5', text: '#047857', border: '#A7F3D0' },
          peach: { bg: '#FFF7ED', text: '#C2410C', border: '#FED7AA' },
          sky: { bg: '#EFF6FF', text: '#1D4ED8', border: '#BFDBFE' },
        }
      }
    },
  },
  plugins: [],
}
