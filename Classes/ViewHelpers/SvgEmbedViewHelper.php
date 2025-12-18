<?php

/*
 * Copyright 2021 Bastian Schwabe <bas@neuedaten.de>
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Neuedaten\SvgEmbed\ViewHelpers;

use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class SvgEmbedViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    const FAL_ID = 'FAL_ID';
    const FAL_OBJECT = 'FAL_OBJECT';
    const FAL = 'FAL';
    const PATH = 'PATH';
    const ARRAY = 'ARRAY';

    public function initializeArguments()
    {
        $this->registerArgument('src', 'mixed', 'The svg file path to embed', true);
        $this->registerArgument('srcType', 'string', 'src type (FAL_ID, FAL_OBJECT, PATH)', false, self::PATH);
        $this->registerArgument('cleanup', 'bool', 'Remove comments and id attributes', false, false);
    }

    /**
     * @param array                                                      $arguments
     * @param \Closure                                                   $renderChildrenClosure
     * @param \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     *
     * @return false|mixed|string|null
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {

        /** @var string|int|\TYPO3\CMS\Core\Resource\File $src */
        $src = $arguments['src'];

        /** @var string|null $srcType */
        $srcType = $arguments['srcType'];

        $path = null;
        $fileContent = null;

        switch($srcType) {
            case self::FAL_ID:
                $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
                $file = $resourceFactory->getFileObjectFromCombinedIdentifier($src);
                $path = GeneralUtility::getFileAbsFileName(ltrim($file->getPublicUrl(), '/'));
                break;
            case self::FAL_OBJECT:
            case self::FAL:
                $path = GeneralUtility::getFileAbsFileName(ltrim($src->getPublicUrl(), '/'));
                break;

            case self::ARRAY:
                if (array_key_exists('url', $src)) {
                    $path = GeneralUtility::getFileAbsFileName(ltrim($src['url'], '/'));
                }
                break;
            case self::PATH:
            default:
                $path = GeneralUtility::getFileAbsFileName($src);
        }

        if ($path) {
            if (pathinfo($path, PATHINFO_EXTENSION) !== 'svg') {
                return null;
            }

            try {
                $fileContent = file_get_contents($path);
            } catch (\Exception $e) {
                return null;
            }
        }

        if ($arguments['cleanup'] && $fileContent) {
           $fileContent = self::cleanUpSvg($fileContent);
        }

        return $fileContent;
    }

    private static function cleanUpSvg(string $svgContent): string
    {
        $svgContent = preg_replace('/<!--(.*?)-->/', '', $svgContent);
        $svgContent = preg_replace('/\s+id="[^"]*"/', '', $svgContent);
        $svgContent = preg_replace('/<\?xml.*?\?>/', '', $svgContent);
        return $svgContent;
    }
}
