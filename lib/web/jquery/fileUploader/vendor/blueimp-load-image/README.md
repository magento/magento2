# JavaScript Load Image

> A JavaScript library to load and transform image files.

## Contents

- [Demo](https://blueimp.github.io/JavaScript-Load-Image/)
- [Description](#description)
- [Setup](#setup)
- [Usage](#usage)
  - [Image loading](#image-loading)
  - [Image scaling](#image-scaling)
- [Requirements](#requirements)
- [Browser support](#browser-support)
- [API](#api)
  - [Callback](#callback)
    - [Function signature](#function-signature)
    - [Cancel image loading](#cancel-image-loading)
    - [Callback arguments](#callback-arguments)
    - [Error handling](#error-handling)
  - [Promise](#promise)
- [Options](#options)
  - [maxWidth](#maxwidth)
  - [maxHeight](#maxheight)
  - [minWidth](#minwidth)
  - [minHeight](#minheight)
  - [sourceWidth](#sourcewidth)
  - [sourceHeight](#sourceheight)
  - [top](#top)
  - [right](#right)
  - [bottom](#bottom)
  - [left](#left)
  - [contain](#contain)
  - [cover](#cover)
  - [aspectRatio](#aspectratio)
  - [pixelRatio](#pixelratio)
  - [downsamplingRatio](#downsamplingratio)
  - [imageSmoothingEnabled](#imagesmoothingenabled)
  - [imageSmoothingQuality](#imagesmoothingquality)
  - [crop](#crop)
  - [orientation](#orientation)
  - [meta](#meta)
  - [canvas](#canvas)
  - [crossOrigin](#crossorigin)
  - [noRevoke](#norevoke)
- [Metadata parsing](#metadata-parsing)
  - [Image head](#image-head)
  - [Exif parser](#exif-parser)
    - [Exif Thumbnail](#exif-thumbnail)
    - [Exif IFD](#exif-ifd)
    - [GPSInfo IFD](#gpsinfo-ifd)
    - [Interoperability IFD](#interoperability-ifd)
    - [Exif parser options](#exif-parser-options)
  - [Exif writer](#exif-writer)
  - [IPTC parser](#iptc-parser)
    - [IPTC parser options](#iptc-parser-options)
- [License](#license)
- [Credits](#credits)

## Description

JavaScript Load Image is a library to load images provided as `File` or `Blob`
objects or via `URL`. It returns an optionally **scaled**, **cropped** or
**rotated** HTML `img` or `canvas` element.

It also provides methods to parse image metadata to extract
[IPTC](https://iptc.org/standards/photo-metadata/) and
[Exif](https://en.wikipedia.org/wiki/Exif) tags as well as embedded thumbnail
images, to overwrite the Exif Orientation value and to restore the complete
image header after resizing.

## Setup

Install via [NPM](https://www.npmjs.com/package/blueimp-load-image):

```sh
npm install blueimp-load-image
```

This will install the JavaScript files inside
`./node_modules/blueimp-load-image/js/` relative to your current directory, from
where you can copy them into a folder that is served by your web server.

Next include the combined and minified JavaScript Load Image script in your HTML
markup:

```html
<script src="js/load-image.all.min.js"></script>
```

Or alternatively, choose which components you want to include:

```html
<!-- required for all operations -->
<script src="js/load-image.js"></script>

<!-- required for scaling, cropping and as dependency for rotation -->
<script src="js/load-image-scale.js"></script>

<!-- required to parse meta data and to restore the complete image head -->
<script src="js/load-image-meta.js"></script>

<!-- required to parse meta data from images loaded via URL -->
<script src="js/load-image-fetch.js"></script>

<!-- required for rotation and cross-browser image orientation -->
<script src="js/load-image-orientation.js"></script>

<!-- required to parse Exif tags and cross-browser image orientation -->
<script src="js/load-image-exif.js"></script>

<!-- required to display text mappings for Exif tags -->
<script src="js/load-image-exif-map.js"></script>

<!-- required to parse IPTC tags -->
<script src="js/load-image-iptc.js"></script>

<!-- required to display text mappings for IPTC tags -->
<script src="js/load-image-iptc-map.js"></script>
```

## Usage

### Image loading

In your application code, use the `loadImage()` function with
[callback](#callback) style:

```js
document.getElementById('file-input').onchange = function () {
  loadImage(
    this.files[0],
    function (img) {
      document.body.appendChild(img)
    },
    { maxWidth: 600 } // Options
  )
}
```

Or use the [Promise](#promise) based API like this ([requires](#requirements) a
polyfill for older browsers):

```js
document.getElementById('file-input').onchange = function () {
  loadImage(this.files[0], { maxWidth: 600 }).then(function (data) {
    document.body.appendChild(data.image)
  })
}
```

With
[async/await](https://developer.mozilla.org/en-US/docs/Learn/JavaScript/Asynchronous/Async_await)
(requires a modern browser or a code transpiler like
[Babel](https://babeljs.io/) or [TypeScript](https://www.typescriptlang.org/)):

```js
document.getElementById('file-input').onchange = async function () {
  let data = await loadImage(this.files[0], { maxWidth: 600 })
  document.body.appendChild(data.image)
}
```

### Image scaling

It is also possible to use the image scaling functionality directly with an
existing image:

```js
var scaledImage = loadImage.scale(
  img, // img or canvas element
  { maxWidth: 600 }
)
```

## Requirements

The JavaScript Load Image library has zero dependencies, but benefits from the
following two
[polyfills](https://developer.mozilla.org/en-US/docs/Glossary/Polyfill):

- [blueimp-canvas-to-blob](https://github.com/blueimp/JavaScript-Canvas-to-Blob)
  for browsers without native
  [HTMLCanvasElement.toBlob](https://developer.mozilla.org/en-US/docs/Web/API/HTMLCanvasElement/toBlob)
  support, to create `Blob` objects out of `canvas` elements.
- [promise-polyfill](https://github.com/taylorhakes/promise-polyfill) to be able
  to use the
  [Promise](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Promise)
  based `loadImage` API in Browsers without native `Promise` support.

## Browser support

Browsers which implement the following APIs support all options:

- Loading images from File and Blob objects:
  - [URL.createObjectURL](https://developer.mozilla.org/en-US/docs/Web/API/URL/createObjectURL)
    or
    [FileReader.readAsDataURL](https://developer.mozilla.org/en-US/docs/Web/API/FileReader/readAsDataURL)
- Parsing meta data:
  - [FileReader.readAsArrayBuffer](https://developer.mozilla.org/en-US/docs/Web/API/FileReader/readAsArrayBuffer)
  - [Blob.slice](https://developer.mozilla.org/en-US/docs/Web/API/Blob/slice)
  - [DataView](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/DataView)
    (no [BigInt](https://developer.mozilla.org/en-US/docs/Glossary/BigInt)
    support required)
- Parsing meta data from images loaded via URL:
  - [fetch Response.blob](https://developer.mozilla.org/en-US/docs/Web/API/Body/blob)
    or
    [XMLHttpRequest.responseType blob](https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest/responseType#blob)
- Promise based API:
  - [Promise](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Promise)

This includes (but is not limited to) the following browsers:

- Chrome 32+
- Firefox 29+
- Safari 8+
- Mobile Chrome 42+ (Android)
- Mobile Firefox 50+ (Android)
- Mobile Safari 8+ (iOS)
- Edge 74+
- Edge Legacy 12+
- Internet Explorer 10+ `*`

`*` Internet Explorer [requires](#requirements) a polyfill for the `Promise`
based API.

Loading an image from a URL and applying transformations (scaling, cropping and
rotating - except `orientation:true`, which requires reading meta data) is
supported by all browsers which implement the
[HTMLCanvasElement](https://developer.mozilla.org/en-US/docs/Web/API/HTMLCanvasElement)
interface.

Loading an image from a URL and scaling it in size is supported by all browsers
which implement the
[img](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img) element and
has been tested successfully with browser engines as old as Internet Explorer 5
(via
[IE11's emulation mode](<https://docs.microsoft.com/en-us/previous-versions/windows/internet-explorer/ie-developer/samples/dn255001(v=vs.85)>)).

The `loadImage()` function applies options using
[progressive enhancement](https://en.wikipedia.org/wiki/Progressive_enhancement)
and falls back to a configuration that is supported by the browser, e.g. if the
`canvas` element is not supported, an equivalent `img` element is returned.

## API

### Callback

#### Function signature

The `loadImage()` function accepts a
[File](https://developer.mozilla.org/en-US/docs/Web/API/File) or
[Blob](https://developer.mozilla.org/en-US/docs/Web/API/Blob) object or an image
URL as first argument.

If a [File](https://developer.mozilla.org/en-US/docs/Web/API/File) or
[Blob](https://developer.mozilla.org/en-US/docs/Web/API/Blob) is passed as
parameter, it returns an HTML `img` element if the browser supports the
[URL](https://developer.mozilla.org/en-US/docs/Web/API/URL) API, alternatively a
[FileReader](https://developer.mozilla.org/en-US/docs/Web/API/FileReader) object
if the `FileReader` API is supported, or `false`.

It always returns an HTML
[img](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Img) element
when passing an image URL:

```js
var loadingImage = loadImage(
  'https://example.org/image.png',
  function (img) {
    document.body.appendChild(img)
  },
  { maxWidth: 600 }
)
```

#### Cancel image loading

Some browsers (e.g. Chrome) will cancel the image loading process if the `src`
property of an `img` element is changed.  
To avoid unnecessary requests, we can use the
[data URL](https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/Data_URIs)
of a 1x1 pixel transparent GIF image as `src` target to cancel the original
image download.

To disable callback handling, we can also unset the image event handlers and for
maximum browser compatibility, cancel the file reading process if the returned
object is a
[FileReader](https://developer.mozilla.org/en-US/docs/Web/API/FileReader)
instance:

```js
var loadingImage = loadImage(
  'https://example.org/image.png',
  function (img) {
    document.body.appendChild(img)
  },
  { maxWidth: 600 }
)

if (loadingImage) {
  // Unset event handling for the loading image:
  loadingImage.onload = loadingImage.onerror = null

  // Cancel image loading process:
  if (loadingImage.abort) {
    // FileReader instance, stop the file reading process:
    loadingImage.abort()
  } else {
    // HTMLImageElement element, cancel the original image request by changing
    // the target source to the data URL of a 1x1 pixel transparent image GIF:
    loadingImage.src =
      'data:image/gif;base64,' +
      'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
  }
}
```

**Please note:**  
The `img` element (or `FileReader` instance) for the loading image is only
returned when using the callback style API and not available with the
[Promise](#promise) based API.

#### Callback arguments

For the callback style API, the second argument to `loadImage()` must be a
`callback` function, which is called when the image has been loaded or an error
occurred while loading the image.

The callback function is passed two arguments:

1. An HTML [img](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img)
   element or
   [canvas](https://developer.mozilla.org/en-US/docs/Web/API/Canvas_API)
   element, or an
   [Event](https://developer.mozilla.org/en-US/docs/Web/API/Event) object of
   type `error`.
2. An object with the original image dimensions as properties and potentially
   additional [metadata](#metadata-parsing).

```js
loadImage(
  fileOrBlobOrUrl,
  function (img, data) {
    document.body.appendChild(img)
    console.log('Original image width: ', data.originalWidth)
    console.log('Original image height: ', data.originalHeight)
  },
  { maxWidth: 600, meta: true }
)
```

**Please note:**  
The original image dimensions reflect the natural width and height of the loaded
image before applying any transformation.  
For consistent values across browsers, [metadata](#metadata-parsing) parsing has
to be enabled via `meta:true`, so `loadImage` can detect automatic image
orientation and normalize the dimensions.

#### Error handling

Example code implementing error handling:

```js
loadImage(
  fileOrBlobOrUrl,
  function (img, data) {
    if (img.type === 'error') {
      console.error('Error loading image file')
    } else {
      document.body.appendChild(img)
    }
  },
  { maxWidth: 600 }
)
```

### Promise

If the `loadImage()` function is called without a `callback` function as second
argument and the
[Promise](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Promise)
API is available, it returns a `Promise` object:

```js
loadImage(fileOrBlobOrUrl, { maxWidth: 600, meta: true })
  .then(function (data) {
    document.body.appendChild(data.image)
    console.log('Original image width: ', data.originalWidth)
    console.log('Original image height: ', data.originalHeight)
  })
  .catch(function (err) {
    // Handling image loading errors
    console.log(err)
  })
```

The `Promise` resolves with an object with the following properties:

- `image`: An HTML
  [img](https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img) or
  [canvas](https://developer.mozilla.org/en-US/docs/Web/API/Canvas_API) element.
- `originalWidth`: The original width of the image.
- `originalHeight`: The original height of the image.

Please also read the note about original image dimensions normalization in the
[callback arguments](#callback-arguments) section.

If [metadata](#metadata-parsing) has been parsed, additional properties might be
present on the object.

If image loading fails, the `Promise` rejects with an
[Event](https://developer.mozilla.org/en-US/docs/Web/API/Event) object of type
`error`.

## Options

The optional options argument to `loadImage()` allows to configure the image
loading.

It can be used the following way with the callback style:

```js
loadImage(
  fileOrBlobOrUrl,
  function (img) {
    document.body.appendChild(img)
  },
  {
    maxWidth: 600,
    maxHeight: 300,
    minWidth: 100,
    minHeight: 50,
    canvas: true
  }
)
```

Or the following way with the `Promise` based API:

```js
loadImage(fileOrBlobOrUrl, {
  maxWidth: 600,
  maxHeight: 300,
  minWidth: 100,
  minHeight: 50,
  canvas: true
}).then(function (data) {
  document.body.appendChild(data.image)
})
```

All settings are optional. By default, the image is returned as HTML `img`
element without any image size restrictions.

### maxWidth

Defines the maximum width of the `img`/`canvas` element.

### maxHeight

Defines the maximum height of the `img`/`canvas` element.

### minWidth

Defines the minimum width of the `img`/`canvas` element.

### minHeight

Defines the minimum height of the `img`/`canvas` element.

### sourceWidth

The width of the sub-rectangle of the source image to draw into the destination
canvas.  
Defaults to the source image width and requires `canvas: true`.

### sourceHeight

The height of the sub-rectangle of the source image to draw into the destination
canvas.  
Defaults to the source image height and requires `canvas: true`.

### top

The top margin of the sub-rectangle of the source image.  
Defaults to `0` and requires `canvas: true`.

### right

The right margin of the sub-rectangle of the source image.  
Defaults to `0` and requires `canvas: true`.

### bottom

The bottom margin of the sub-rectangle of the source image.  
Defaults to `0` and requires `canvas: true`.

### left

The left margin of the sub-rectangle of the source image.  
Defaults to `0` and requires `canvas: true`.

### contain

Scales the image up/down to contain it in the max dimensions if set to `true`.  
This emulates the CSS feature
[background-image: contain](https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Backgrounds_and_Borders/Resizing_background_images#contain).

### cover

Scales the image up/down to cover the max dimensions with the image dimensions
if set to `true`.  
This emulates the CSS feature
[background-image: cover](https://developer.mozilla.org/en-US/docs/Web/CSS/CSS_Backgrounds_and_Borders/Resizing_background_images#cover).

### aspectRatio

Crops the image to the given aspect ratio (e.g. `16/9`).  
Setting the `aspectRatio` also enables the `crop` option.

### pixelRatio

Defines the ratio of the canvas pixels to the physical image pixels on the
screen.  
Should be set to
[window.devicePixelRatio](https://developer.mozilla.org/en-US/docs/Web/API/Window/devicePixelRatio)
unless the scaled image is not rendered on screen.  
Defaults to `1` and requires `canvas: true`.

### downsamplingRatio

Defines the ratio in which the image is downsampled (scaled down in steps).  
By default, images are downsampled in one step.  
With a ratio of `0.5`, each step scales the image to half the size, before
reaching the target dimensions.  
Requires `canvas: true`.

### imageSmoothingEnabled

If set to `false`,
[disables image smoothing](https://developer.mozilla.org/en-US/docs/Web/API/CanvasRenderingContext2D/imageSmoothingEnabled).  
Defaults to `true` and requires `canvas: true`.

### imageSmoothingQuality

Sets the
[quality of image smoothing](https://developer.mozilla.org/en-US/docs/Web/API/CanvasRenderingContext2D/imageSmoothingQuality).  
Possible values: `'low'`, `'medium'`, `'high'`  
Defaults to `'low'` and requires `canvas: true`.

### crop

Crops the image to the `maxWidth`/`maxHeight` constraints if set to `true`.  
Enabling the `crop` option also enables the `canvas` option.

### orientation

Transform the canvas according to the specified Exif orientation, which can be
an `integer` in the range of `1` to `8` or the boolean value `true`.

When set to `true`, it will set the orientation value based on the Exif data of
the image, which will be parsed automatically if the Exif extension is
available.

Exif orientation values to correctly display the letter F:

```
    1             2
  ██████        ██████
  ██                ██
  ████            ████
  ██                ██
  ██                ██

    3             4
      ██        ██
      ██        ██
    ████        ████
      ██        ██
  ██████        ██████

    5             6
██████████    ██
██  ██        ██  ██
██            ██████████

    7             8
        ██    ██████████
    ██  ██        ██  ██
██████████            ██
```

Setting `orientation` to `true` enables the `canvas` and `meta` options, unless
the browser supports automatic image orientation (see
[browser support for image-orientation](https://caniuse.com/#feat=css-image-orientation)).

Setting `orientation` to `1` enables the `canvas` and `meta` options if the
browser does support automatic image orientation (to allow reset of the
orientation).

Setting `orientation` to an integer in the range of `2` to `8` always enables
the `canvas` option and also enables the `meta` option if the browser supports
automatic image orientation (again to allow reset).

### meta

Automatically parses the image metadata if set to `true`.

If metadata has been found, the data object passed as second argument to the
callback function has additional properties (see
[metadata parsing](#metadata-parsing)).

If the file is given as URL and the browser supports the
[fetch API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API) or the
XHR
[responseType](https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest/responseType)
`blob`, fetches the file as `Blob` to be able to parse the metadata.

### canvas

Returns the image as
[canvas](https://developer.mozilla.org/en-US/docs/Web/API/Canvas_API) element if
set to `true`.

### crossOrigin

Sets the `crossOrigin` property on the `img` element for loading
[CORS enabled images](https://developer.mozilla.org/en-US/docs/Web/HTML/CORS_enabled_image).

### noRevoke

By default, the
[created object URL](https://developer.mozilla.org/en-US/docs/Web/API/URL/createObjectURL)
is revoked after the image has been loaded, except when this option is set to
`true`.

## Metadata parsing

If the Load Image Meta extension is included, it is possible to parse image meta
data automatically with the `meta` option:

```js
loadImage(
  fileOrBlobOrUrl,
  function (img, data) {
    console.log('Original image head: ', data.imageHead)
    console.log('Exif data: ', data.exif) // requires exif extension
    console.log('IPTC data: ', data.iptc) // requires iptc extension
  },
  { meta: true }
)
```

Or alternatively via `loadImage.parseMetaData`, which can be used with an
available `File` or `Blob` object as first argument:

```js
loadImage.parseMetaData(
  fileOrBlob,
  function (data) {
    console.log('Original image head: ', data.imageHead)
    console.log('Exif data: ', data.exif) // requires exif extension
    console.log('IPTC data: ', data.iptc) // requires iptc extension
  },
  {
    maxMetaDataSize: 262144
  }
)
```

Or using the
[Promise](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Promise)
based API:

```js
loadImage
  .parseMetaData(fileOrBlob, {
    maxMetaDataSize: 262144
  })
  .then(function (data) {
    console.log('Original image head: ', data.imageHead)
    console.log('Exif data: ', data.exif) // requires exif extension
    console.log('IPTC data: ', data.iptc) // requires iptc extension
  })
```

The Metadata extension adds additional options used for the `parseMetaData`
method:

- `maxMetaDataSize`: Maximum number of bytes of metadata to parse.
- `disableImageHead`: Disable parsing the original image head.
- `disableMetaDataParsers`: Disable parsing metadata (image head only)

### Image head

Resized JPEG images can be combined with their original image head via
`loadImage.replaceHead`, which requires the resized image as `Blob` object as
first argument and an `ArrayBuffer` image head as second argument.

With callback style, the third argument must be a `callback` function, which is
called with the new `Blob` object:

```js
loadImage(
  fileOrBlobOrUrl,
  function (img, data) {
    if (data.imageHead) {
      img.toBlob(function (blob) {
        loadImage.replaceHead(blob, data.imageHead, function (newBlob) {
          // do something with the new Blob object
        })
      }, 'image/jpeg')
    }
  },
  { meta: true, canvas: true, maxWidth: 800 }
)
```

Or using the
[Promise](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Promise)
based API like this:

```js
loadImage(fileOrBlobOrUrl, { meta: true, canvas: true, maxWidth: 800 })
  .then(function (data) {
    if (!data.imageHead) throw new Error('Could not parse image metadata')
    return new Promise(function (resolve) {
      data.image.toBlob(function (blob) {
        data.blob = blob
        resolve(data)
      }, 'image/jpeg')
    })
  })
  .then(function (data) {
    return loadImage.replaceHead(data.blob, data.imageHead)
  })
  .then(function (blob) {
    // do something with the new Blob object
  })
  .catch(function (err) {
    console.error(err)
  })
```

**Please note:**  
`Blob` objects of resized images can be created via
[HTMLCanvasElement.toBlob](https://developer.mozilla.org/en-US/docs/Web/API/HTMLCanvasElement/toBlob).  
[blueimp-canvas-to-blob](https://github.com/blueimp/JavaScript-Canvas-to-Blob)
provides a polyfill for browsers without native `canvas.toBlob()` support.

### Exif parser

If you include the Load Image Exif Parser extension, the argument passed to the
callback for `parseMetaData` will contain the following additional properties if
Exif data could be found in the given image:

- `exif`: The parsed Exif tags
- `exifOffsets`: The parsed Exif tag offsets
- `exifTiffOffset`: TIFF header offset (used for offset pointers)
- `exifLittleEndian`: little endian order if true, big endian if false

The `exif` object stores the parsed Exif tags:

```js
var orientation = data.exif[0x0112] // Orientation
```

The `exif` and `exifOffsets` objects also provide a `get()` method to retrieve
the tag value/offset via the tag's mapped name:

```js
var orientation = data.exif.get('Orientation')
var orientationOffset = data.exifOffsets.get('Orientation')
```

By default, only the following names are mapped:

- `Orientation`
- `Thumbnail` (see [Exif Thumbnail](#exif-thumbnail))
- `Exif` (see [Exif IFD](#exif-ifd))
- `GPSInfo` (see [GPSInfo IFD](#gpsinfo-ifd))
- `Interoperability` (see [Interoperability IFD](#interoperability-ifd))

If you also include the Load Image Exif Map library, additional tag mappings
become available, as well as three additional methods:

- `exif.getText()`
- `exif.getName()`
- `exif.getAll()`

```js
var orientationText = data.exif.getText('Orientation') // e.g. "Rotate 90° CW"

var name = data.exif.getName(0x0112) // "Orientation"

// A map of all parsed tags with their mapped names/text as keys/values:
var allTags = data.exif.getAll()
```

#### Exif Thumbnail

Example code displaying a thumbnail image embedded into the Exif metadata:

```js
loadImage(
  fileOrBlobOrUrl,
  function (img, data) {
    var exif = data.exif
    var thumbnail = exif && exif.get('Thumbnail')
    var blob = thumbnail && thumbnail.get('Blob')
    if (blob) {
      loadImage(
        blob,
        function (thumbImage) {
          document.body.appendChild(thumbImage)
        },
        { orientation: exif.get('Orientation') }
      )
    }
  },
  { meta: true }
)
```

#### Exif IFD

Example code displaying data from the Exif IFD (Image File Directory) that
contains Exif specified TIFF tags:

```js
loadImage(
  fileOrBlobOrUrl,
  function (img, data) {
    var exifIFD = data.exif && data.exif.get('Exif')
    if (exifIFD) {
      // Map of all Exif IFD tags with their mapped names/text as keys/values:
      console.log(exifIFD.getAll())
      // A specific Exif IFD tag value:
      console.log(exifIFD.get('UserComment'))
    }
  },
  { meta: true }
)
```

#### GPSInfo IFD

Example code displaying data from the Exif IFD (Image File Directory) that
contains [GPS](https://en.wikipedia.org/wiki/Global_Positioning_System) info:

```js
loadImage(
  fileOrBlobOrUrl,
  function (img, data) {
    var gpsInfo = data.exif && data.exif.get('GPSInfo')
    if (gpsInfo) {
      // Map of all GPSInfo tags with their mapped names/text as keys/values:
      console.log(gpsInfo.getAll())
      // A specific GPSInfo tag value:
      console.log(gpsInfo.get('GPSLatitude'))
    }
  },
  { meta: true }
)
```

#### Interoperability IFD

Example code displaying data from the Exif IFD (Image File Directory) that
contains Interoperability data:

```js
loadImage(
  fileOrBlobOrUrl,
  function (img, data) {
    var interoperabilityData = data.exif && data.exif.get('Interoperability')
    if (interoperabilityData) {
      // The InteroperabilityIndex tag value:
      console.log(interoperabilityData.get('InteroperabilityIndex'))
    }
  },
  { meta: true }
)
```

#### Exif parser options

The Exif parser adds additional options:

- `disableExif`: Disables Exif parsing when `true`.
- `disableExifOffsets`: Disables storing Exif tag offsets when `true`.
- `includeExifTags`: A map of Exif tags to include for parsing (includes all but
  the excluded tags by default).
- `excludeExifTags`: A map of Exif tags to exclude from parsing (defaults to
  exclude `Exif` `MakerNote`).

An example parsing only Orientation, Thumbnail and ExifVersion tags:

```js
loadImage.parseMetaData(
  fileOrBlob,
  function (data) {
    console.log('Exif data: ', data.exif)
  },
  {
    includeExifTags: {
      0x0112: true, // Orientation
      ifd1: {
        0x0201: true, // JPEGInterchangeFormat (Thumbnail data offset)
        0x0202: true // JPEGInterchangeFormatLength (Thumbnail data length)
      },
      0x8769: {
        // ExifIFDPointer
        0x9000: true // ExifVersion
      }
    }
  }
)
```

An example excluding `Exif` `MakerNote` and `GPSInfo`:

```js
loadImage.parseMetaData(
  fileOrBlob,
  function (data) {
    console.log('Exif data: ', data.exif)
  },
  {
    excludeExifTags: {
      0x8769: {
        // ExifIFDPointer
        0x927c: true // MakerNote
      },
      0x8825: true // GPSInfoIFDPointer
    }
  }
)
```

### Exif writer

The Exif parser extension also includes a minimal writer that allows to override
the Exif `Orientation` value in the parsed `imageHead` `ArrayBuffer`:

```js
loadImage(
  fileOrBlobOrUrl,
  function (img, data) {
    if (data.imageHead && data.exif) {
      // Reset Exif Orientation data:
      loadImage.writeExifData(data.imageHead, data, 'Orientation', 1)
      img.toBlob(function (blob) {
        loadImage.replaceHead(blob, data.imageHead, function (newBlob) {
          // do something with newBlob
        })
      }, 'image/jpeg')
    }
  },
  { meta: true, orientation: true, canvas: true, maxWidth: 800 }
)
```

**Please note:**  
The Exif writer relies on the Exif tag offsets being available as
`data.exifOffsets` property, which requires that Exif data has been parsed from
the image.  
The Exif writer can only change existing values, not add new tags, e.g. it
cannot add an Exif `Orientation` tag for an image that does not have one.

### IPTC parser

If you include the Load Image IPTC Parser extension, the argument passed to the
callback for `parseMetaData` will contain the following additional properties if
IPTC data could be found in the given image:

- `iptc`: The parsed IPTC tags
- `iptcOffsets`: The parsed IPTC tag offsets

The `iptc` object stores the parsed IPTC tags:

```js
var objectname = data.iptc[5]
```

The `iptc` and `iptcOffsets` objects also provide a `get()` method to retrieve
the tag value/offset via the tag's mapped name:

```js
var objectname = data.iptc.get('ObjectName')
```

By default, only the following names are mapped:

- `ObjectName`

If you also include the Load Image IPTC Map library, additional tag mappings
become available, as well as three additional methods:

- `iptc.getText()`
- `iptc.getName()`
- `iptc.getAll()`

```js
var keywords = data.iptc.getText('Keywords') // e.g.: ['Weather','Sky']

var name = data.iptc.getName(5) // ObjectName

// A map of all parsed tags with their mapped names/text as keys/values:
var allTags = data.iptc.getAll()
```

#### IPTC parser options

The IPTC parser adds additional options:

- `disableIptc`: Disables IPTC parsing when true.
- `disableIptcOffsets`: Disables storing IPTC tag offsets when `true`.
- `includeIptcTags`: A map of IPTC tags to include for parsing (includes all but
  the excluded tags by default).
- `excludeIptcTags`: A map of IPTC tags to exclude from parsing (defaults to
  exclude `ObjectPreviewData`).

An example parsing only the `ObjectName` tag:

```js
loadImage.parseMetaData(
  fileOrBlob,
  function (data) {
    console.log('IPTC data: ', data.iptc)
  },
  {
    includeIptcTags: {
      5: true // ObjectName
    }
  }
)
```

An example excluding `ApplicationRecordVersion` and `ObjectPreviewData`:

```js
loadImage.parseMetaData(
  fileOrBlob,
  function (data) {
    console.log('IPTC data: ', data.iptc)
  },
  {
    excludeIptcTags: {
      0: true, // ApplicationRecordVersion
      202: true // ObjectPreviewData
    }
  }
)
```

## License

The JavaScript Load Image library is released under the
[MIT license](https://opensource.org/licenses/MIT).

## Credits

- Original image metadata handling implemented with the help and contribution of
  Achim Stöhr.
- Original Exif tags mapping based on Jacob Seidelin's
  [exif-js](https://github.com/exif-js/exif-js) library.
- Original IPTC parser implementation by
  [Dave Bevan](https://github.com/bevand10).
