const ExtractTextPlugin = require('extract-text-webpack-plugin')
const path = require('path')
const webpack = require('webpack')
const fs = require('fs');

const bowerConfig = JSON.parse(fs.readFileSync('./bower.json', 'utf8'));

const config = {
  context: path.resolve(__dirname, './'),
  entry: {
    app: './assets/main.js'
  },
  output: {
    filename: './dist/main.js'
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
    new webpack.DefinePlugin({
      BOWER_DEPS: JSON.stringify(Object.keys(bowerConfig.dependencies).reduce((result, key) => {
        result[key] = bowerConfig.dependencies[key].split('.').slice(0, -1).join('.')
        return result
      }, {}))
    })
  ],
}

module.exports = config
