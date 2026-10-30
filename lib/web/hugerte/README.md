# HugeRTE

The 100% free fork of the world's #1 open source rich text editor.

Used and trusted by millions of developers, [TinyMCE](https://github.com/tinymce/tinymce) (the original project we've forked) is the world’s most customizable, scalable, and flexible rich text editor. However, they changed the license of TinyMCE 7 to GPLv2+ (or a commercial license) while it has been MIT for TinyMCE 6 and LGPL for older versions. This creates problems for users (see [the discussion](https://github.com/tinymce/tinymce/issues/9453)) so a fork has been created here. It has originally been named HugeMCE, however, due to [potential trademark confusion with TinyMCE](https://github.com/hugerte/hugerte/issues/1#issuecomment-2373423311), it has been renamed to HugeRTE before its publishment to npm.

## Get started with HugeRTE

The simplest way to get started with HugeRTE is using a CDN:

```html
<script src="https://cdn.jsdelivr.net/npm/hugerte@1.0.4/hugerte.min.js">
```

Or install it manually via `npm`:

```bash
npm i hugerte
```

We're soon going to host the docs for HugeRTE on [our website](https://hugerte.org); for now, please refer to the [TinyMCE docs](https://tiny.cloud/docs/tinymce/6), but replace `tinymce` by `hugerte` in all code snippets.

[See this guide also](https://www.tiny.cloud/docs/tinymce/6/npm-projects/), but replace `tinymce@^6` by `hugerte@^1` (and of course, all occurrences of `tinymce` by `hugerte`).

HugeRTE provides a range of configuration options that allow you to integrate it into your application. Start customizing with a [basic setup](https://www.tiny.cloud/docs/tinymce/6/basic-setup/).

Configure it for one of three modes of editing:

- [classic editing mode](https://www.tiny.cloud/docs/tinymce/6/use-tinymce-classic/).
- [inline editing mode](https://www.tiny.cloud/docs/tinymce/6/use-tinymce-inline/).
- [distraction-free editing mode](https://www.tiny.cloud/docs/tinymce/6/use-tinymce-distraction-free/).

## Migrate from TinyMCE

If you have been using TinyMCE before, you have to brute-force replace `tinymce` by `hugerte` in your code. In your package.json, make sure you use `1.0.4` as `hugerte` version – not the one you used for `tinymce` before. HugeRTE is based on TinyMCE 6.8.4, but it is even later because it contains some code from TinyMCE 7 (all until the [commit which changed the license](https://github.com/tinymce/tinymce/commit/1cfe7f6817c68d713971a3e1dbe0c9775a40ce6d)). See the [Changelog](https://hugerte.org/docs/hugerte/1/changelog/) for details.

## Features

### Integration

> [!WARNING]
> We have not yet forked these integrations so they're still about TinyMCE.

TinyMCE is easily integrated into your projects with the help of components such as:

- [tinymce-react](https://github.com/tinymce/tinymce-react)
- [tinymce-vue](https://github.com/tinymce/tinymce-vue)
- [tinymce-angular](https://github.com/tinymce/tinymce-angular)

With over 29 integrations, and 400+ APIs, see the TinyMCE docs for a full list of editor [integrations](https://www.tiny.cloud/docs/tinymce/6/integrations/).

### Customization

It is easy to [configure the UI](https://www.tiny.cloud/docs/tinymce/6/customize-ui/) of your rich text editor to match the design of your site, product or application. Due to its flexibility, you can [configure the editor](https://www.tiny.cloud/docs/tinymce/6/basic-setup/) with as much or as little functionality as you like, depending on your requirements.

With [30 powerful plugins available](https://www.tiny.cloud/tinymce/features/), and content editable as the basis of HugeRTE, adding additional functionality is as simple as including a single line of code.

Realizing the full power of most plugins requires only a few lines more.

### Extensibility

Sometimes your editor requirements can be quite unique, and you need the freedom and flexibility to innovate. Thanks to HugeRTE being open source, you can view the source code and develop your own extensions for custom functionality to meet your own requirements.

The HugeRTE [API](https://www.tiny.cloud/docs/tinymce/6/apis/tinymce.root/) is exposed to make it easier for you to write custom functionality that fits within the existing framework of HugeRTE [UI components](https://www.tiny.cloud/docs/tinymce/6/custom-ui-components/). Just don't forget to replace every instance of the `tinymce` object in the TinyMCE docs by `hugerte`.

## Compiling and contributing

In 2019 TinyMCE made the decision to transition their codebase to a monorepo. For information on compiling and contributing, see: [contribution guidelines](https://github.com/tinymce/tinymce/blob/master/CONTRIBUTING.md).

As an open source product, we encourage and support the active development of our software.

## Want more information?

Visit the [HugeRTE website](https://hugerte.org) and check out the [TinyMCE documentation](https://www.tiny.cloud/docs/) until we host the docs ourselves.
