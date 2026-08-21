# SVG embed view helper for TYPO3 CMS

Fluid view helper that embeds the contents of an SVG file inline into the rendered markup,
so the SVG can be styled and scripted like any other DOM element.

## Requirements

| Extension version | TYPO3        | PHP    |
|-------------------|--------------|--------|
| 2.x               | 13.4 and 14  | >= 8.2 |
| 1.0.0, 0.10.x     | 9.5 – 12.4   | >= 7.4 |

## Install

`composer req neuedaten/svg-embed`

## Use

### Load namespace

Since version 2.0.0 the `neuedaten` namespace is registered globally, so this line is optional:

`{namespace neuedaten=Neuedaten\SvgEmbed\ViewHelpers}`

### Use view helper

#### File:
`<neuedaten:svgEmbed src="EXT:your_extension/Resources/Public/Images/filename.svg"/>`
#### FAL:
`<neuedaten:svgEmbed src="{falObject}" srcType="FAL_OBJECT"/>`

Accepts both `File` and `FileReference` objects.
#### FAL id:
`<neuedaten:svgEmbed src="{id}" srcType="FAL_ID"/>`

Expects a combined identifier, e.g. `1:/user_upload/logo.svg`.
#### ARRAY:
(an array like you get some times in a flux template)
`<neuedaten:svgEmbed src="{array}" srcType="ARRAY"/>`

### Arguments

| Argument  | Type   | Default | Description                                                        |
|-----------|--------|---------|--------------------------------------------------------------------|
| `src`     | mixed  | –       | Path, FAL object, combined identifier or array (required)          |
| `srcType` | string | `PATH`  | `PATH`, `FAL`, `FAL_OBJECT`, `FAL_ID` or `ARRAY`                   |
| `cleanup` | bool   | `false` | Strip comments, `id` attributes, the XML declaration, `<script>` elements, `on*` event handlers and `javascript:` hrefs |

Anything that is not an `.svg` file, or cannot be read, renders as empty output.

> **Note on `cleanup`:** the script and event handler removal is a hardening measure for
> editor-uploaded files. It is *not* a complete SVG sanitizer — do not treat it as a security
> boundary for untrusted input.

## Upgrading to 2.0.0

Version 2.0.0 drops support for TYPO3 12 and older. The view helper was migrated from the
removed `renderStatic()` API to `render()` (required by Fluid 5 in TYPO3 14), and FAL files are
now read through the storage API instead of resolving their public URL to a local path.

Template syntax is unchanged — no adjustments are needed in your Fluid templates. Installations
on TYPO3 12 or older should stay on `1.0.0`.
