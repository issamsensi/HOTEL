module.exports = {
  content: [
    "./src//*.{html,js,php}",
    "./includes//*.{html,js,php}"
  ],
  theme: {
    extend: {
      colors: {
        primary: '#0d4a9d',
        secondary: '#0a1a2b',
        accent: '#ffc107',
      },
      fontFamily: {
        inter: ['Inter', 'sans-serif'],
      },
      backgroundImage: {
        'gradient-primary': 'linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%)',
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}