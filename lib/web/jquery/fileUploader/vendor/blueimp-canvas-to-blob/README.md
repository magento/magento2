# JavaScript Canvas to Blob

## Contents

- [Description](#description)
- [Setup](#setup)
- [Usage](#usage)
- [Requirements](#requirements)
- [Browsers](#browsers)
- [API](#api)
- [Test](#test)
- [License](#license)

## Description

Canvas to Blob is a
[polyfill](https://developer.mozilla.org/en-US/docs/Glossary/Polyfill) for
Browsers that don't support the standard JavaScript
[HTMLCanvasElement.toBlob](https://developer.mozilla.org/en-US/docs/Web/API/HTMLCanvasElement/toBlob)
method.

It can be used to create
[Blob](https://developer.mozilla.org/en-US/docs/Web/API/Blob) objects from an
HTML [canvas](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/canvas)
element.

## Setup

Install via [NPM](https://www.npmjs.com/package/blueimp-canvas-to-blob):

```sh
npm install blueimp-canvas-to-blob
```

This will install the JavaScript files inside
`./node_modules/blueimp-canvas-to-blob/js/` relative to your current directory,
from where you can copy them into a folder that is served by your web server.

Next include the minified JavaScript Canvas to Blob script in your HTML markup:

```html
<script src="js/canvas-to-blob.min.js"></script>
```

Or alternatively, include the non-minified version:

```html
<script src="js/canvas-to-blob.js"></script>
```

## Usage

You can use the `canvas.toBlob()` method in the same way as the native
implementation:

```js
var canvas = document.createElement('canvas')
// Edit the canvas ...
if (canvas.toBlob) {
  canvas.toBlob(function (blob) {
    // Do something with the blob object,
    // e.g. create multipart form data for file uploads:
    var formData = new FormData()
    formData.append('file', blob, 'image.jpg')
    // ...
  }, 'image/jpeg')
}
```

## Requirements

The JavaScript Canvas to Blob function has zero dependencies.

However, it is a very suitable complement to the
[JavaScript Load Image](https://github.com/blueimp/JavaScript-Load-Image)
function.

## Browsers

The following browsers have native support for
[HTMLCanvasElement.toBlob](https://developer.mozilla.org/en-US/docs/Web/API/HTMLCanvasElement/toBlob):

- Chrome 50+
- Firefox 19+
- Safari 11+
- Mobile Chrome 50+ (Android)
- Mobile Firefox 4+ (Android)
- Mobile Safari 11+ (iOS)
- Edge 79+

Browsers which implement the following APIs support `canvas.toBlob()` via
polyfill:

- [HTMLCanvasElement](https://developer.mozilla.org/en-US/docs/Web/API/HTMLCanvasElement)
- [HTMLCanvasElement.toDataURL](https://developer.mozilla.org/en-US/docs/Web/API/HTMLCanvasElement/toDataURL)
- [Blob() constructor](https://developer.mozilla.org/en-US/docs/Web/API/Blob/Blob)
- [atob](https://developer.mozilla.org/en-US/docs/Web/API/WindowOrWorkerGlobalScope/atob)
- [ArrayBuffer](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/ArrayBuffer)
- [Uint8Array](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Uint8Array)

This includes the following browsers:

- Chrome 20+
- Firefox 13+
- Safari 8+
- Mobile Chrome 25+ (Android)
- Mobile Firefox 14+ (Android)
- Mobile Safari 8+ (iOS)
- Edge 74+
- Edge Legacy 12+
- Internet Explorer 10+

## API

In addition to the `canvas.toBlob()` polyfill, the JavaScript Canvas to Blob
script exposes its helper function `dataURLtoBlob(url)`:

```js
// Uncomment the following line when using a module loader like webpack:
// var dataURLtoBlob = require('blueimp-canvas-to-blob')

// black+white 3x2 GIF, base64 data:
var b64 = 'R0lGODdhAwACAPEAAAAAAP///yZFySZFySH5BAEAAAIALAAAAAADAAIAAAIDRAJZADs='
var url = 'data:image/gif;base64,' + b64
var blob = dataURLtoBlob(url)
```

## Test

[Unit tests](https://blueimp.github.io/JavaScript-Canvas-to-Blob/test/)

## License

The JavaScript Canvas to Blob script is released under the
[MIT license](https://opensource.org/licenses/MIT).
