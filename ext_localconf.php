<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

// Register the ViewHelper namespace globally so templates can use <neuedaten:svgEmbed />
// without declaring {namespace neuedaten=Neuedaten\SvgEmbed\ViewHelpers} first.
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['neuedaten'][] =
    'Neuedaten\\SvgEmbed\\ViewHelpers';
