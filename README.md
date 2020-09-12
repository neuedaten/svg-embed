# SVG embed view helper for TYPO3 CMS

## install:

`composer req neuedaten/svg-embed`

## Use:

### load namespace:
`{namespace neuedaten=Neuedaten\SvgEmbed\ViewHelpers}`

### Use view helper:
#### File:
`<neuedaten:svgEmbed src="EXT:your_extension/Resources/Public/Images/filename.svg"/>`
#### FAL:
`<neuedaten:svgEmbed src="{falObject}" srcType="FAL"/>`
#### FAL id:
`<neuedaten:svgEmbed src="{id}" srcType="FAL_ID"/>`