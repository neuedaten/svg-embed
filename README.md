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
`<neuedaten:svgEmbed src="{falObject}" srcType="FAL_OBJECT"/>`
#### FAL id:
`<neuedaten:svgEmbed src="{id}" srcType="FAL_ID"/>`
#### ARRAY:
(an array like you get some times in a flux template)
`<neuedaten:svgEmbed src="{array}" srcType="ARRAY"/>`