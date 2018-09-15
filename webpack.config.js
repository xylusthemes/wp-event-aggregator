const path = require( 'path' );
const webpack = require( 'webpack' );

module.exports = {
  entry: {
    './assets/js/gutenberg.blocks' : './blocks/index.js',
  },
  output: {
    path: path.resolve( __dirname ),
    filename: '[name].js',
  },
  watch: 'production' !== process.env.NODE_ENV,
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
        },
      },
    ],
  },
};
