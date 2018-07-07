const ExtractTextPlugin = require('extract-text-webpack-plugin')
const path = require('path')

const config = {
  context: path.resolve(__dirname, './'),
  entry: {
    app: './assets/scss/app.scss'
  },
  output: {
    filename: './dist/_.js'
  },
  module: {
    rules: [
      {
        test: /\.(scss|sass)$/,
        loader: ExtractTextPlugin.extract([
          'css-loader', {
            loader: 'fast-sass-loader'
          }
        ])
      },
    ]
  },
  plugins: [
    new ExtractTextPlugin({ // define where to save the file
      filename: './dist/wp-booking-ui.css',
      allChunks: true,
    }),
  ],
}

module.exports = config