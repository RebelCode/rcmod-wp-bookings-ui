global.document = {addEventListener () {}}

var libs = require('./../assets/main.js')
var fs = require('fs')
var URL = require('url')

/**
 * Function for getting adapter for reading from URL.
 */
const adapterFor = (function () {
  var url = require('url'),
    adapters = {
      'http:': require('http'),
      'https:': require('https'),
    }
  return function (inputUrl) {
    return adapters[url.parse(inputUrl).protocol]
  }
}())

/**
 * Download file from URL and write it to destination. Call `cb()` on success.
 *
 * @param {string} url
 * @param {string} dest
 * @param {Function} cb
 */
const download = (url, dest, cb) => {
  var file = fs.createWriteStream(dest)
  var request = adapterFor(url).get(url, function (response) {
    if (response.statusCode >= 300 && response.statusCode < 400 && response.headers.location) {
      let redirectTo = response.headers.location
      if (redirectTo[0] === '/') {
        const paresedUrl = URL.parse(url)
        const origin = paresedUrl.protocol + '//' + paresedUrl.host
        redirectTo = origin + redirectTo
      }
      console.info('Redirect for ' + url + ' to ' + redirectTo)
      download(redirectTo, dest, cb)
      return
    }
    if (response.statusCode === 200) {
      response.pipe(file)
      file.on('finish', function () {
        file.close(cb)
      })
    }
    else {
      throw new Error('Cannot download ' + url + ', response code: ' + response.statusCode)
    }
  })
}

/**
 * Main enter point.
 *
 * @param libs
 */
const main = (libs) => {
  const targetFolder = __dirname + '/../assets/scripts/'
  Object.keys(libs).forEach((libName) => {
    const url = libs[libName] + '.js'
    download(url, targetFolder + libName + '.js', () => {
      console.info(libName + ' is downloaded!')
    })
  })
}
main(libs)
