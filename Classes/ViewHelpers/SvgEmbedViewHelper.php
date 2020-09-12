<?php

/*
 * Copyright 2020 Bastian Schwabe <bas@neuedaten.de>
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
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

class SvgEmbedViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;

    const FAL_ID = 'FAL_ID';
    const FAL_OBJECT = 'FAL_OBJECT';
    const PATH = 'PATH';

    public function initializeArguments()
    {
        $this->registerArgument('src', '', 'The svg file path to embed', true);
        $this->registerArgument('srcType', 'string', 'src type (FAL_ID, FAL_OBJECT, PATH)', false, self::PATH);
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
                $resourceFactory = ResourceFactory::getInstance();
                $file = $resourceFactory->getFileObjectFromCombinedIdentifier($src);
                $path = GeneralUtility::getFileAbsFileName($file->getPublicUrl());
                break;
            case self::FAL_OBJECT:
                $path = GeneralUtility::getFileAbsFileName($src->getPublicUrl());
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

        return $fileContent;
    }
}