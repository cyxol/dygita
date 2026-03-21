module.exports = {
  plugins: [
    require('autoprefixer'),
    require('cssnano')({
      preset: ['default', {
        discardComments: { removeAll: true },
        normalizeWhitespace: true,
        minifySelectors: true,
        minifyFontValues: true,
        minifyParams: true,
        convertValues: true,
        reduceIdents: true,
        colormin: true,
        calc: true
      }]
    })
  ]
};
