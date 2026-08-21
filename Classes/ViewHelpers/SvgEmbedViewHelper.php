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

use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class SvgEmbedViewHelper extends AbstractViewHelper
{
    /**
     * SVG is embedded as raw markup, so it must not be escaped.
     *
     * Note: intentionally left without a native type. AbstractViewHelper declares this
     * property untyped in every Fluid version up to and including Fluid 5, and property
     * types are invariant in PHP.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    const FAL_ID = 'FAL_ID';
    const FAL_OBJECT = 'FAL_OBJECT';
    const FAL = 'FAL';
    const PATH = 'PATH';
    const ARRAY = 'ARRAY';

    public function initializeArguments(): void
    {
        $this->registerArgument('src', 'mixed', 'The svg file path to embed', true);
        $this->registerArgument('srcType', 'string', 'src type (PATH, FAL, FAL_ID, FAL_OBJECT, ARRAY)', false, self::PATH);
        $this->registerArgument('cleanup', 'bool', 'Remove comments, id attributes and script/event handlers', false, false);
    }

    public function render(): ?string
    {
        /** @var string|int|FileInterface|array $src */
        $src = $this->arguments['src'];

        /** @var string|null $srcType */
        $srcType = $this->arguments['srcType'];

        $fileContent = null;

        switch ($srcType) {
            case self::FAL_ID:
                $fileContent = $this->readFalFile($this->resolveFalFile($src));
                break;

            case self::FAL_OBJECT:
            case self::FAL:
                $fileContent = $this->readFalFile($src instanceof FileInterface ? $src : null);
                break;

            case self::ARRAY:
                $fileContent = $this->readLocalFile(
                    is_array($src) && isset($src['url']) ? $this->resolvePath((string)$src['url'], true) : null
                );
                break;

            case self::PATH:
            default:
                $fileContent = $this->readLocalFile($this->resolvePath((string)$src));
        }

        if ($fileContent === null) {
            return null;
        }

        if ($this->arguments['cleanup']) {
            $fileContent = $this->cleanUpSvg($fileContent);
        }

        return $fileContent;
    }

    /**
     * @param string|int $src combined identifier, e.g. "1:/user_upload/logo.svg"
     */
    private function resolveFalFile($src): ?FileInterface
    {
        $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);

        try {
            return $resourceFactory->getFileObjectFromCombinedIdentifier((string)$src);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Reads through the FAL storage driver instead of resolving the public URL to a local
     * path. Since TYPO3 v14 (#107537) public URLs always carry cache busting and relative
     * storage access is restricted, so the URL to path roundtrip is not reliable anymore.
     */
    private function readFalFile(?FileInterface $file): ?string
    {
        if ($file === null || strtolower($file->getExtension()) !== 'svg') {
            return null;
        }

        try {
            return $file->getContents();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param bool $stripLeadingSlash for web paths such as "/fileadmin/x.svg"; must stay off for
     *                                PATH, where a leading slash may denote a real absolute path
     */
    private function resolvePath(string $src, bool $stripLeadingSlash = false): ?string
    {
        // strip a possible cache busting query string before resolving
        $src = trim(strtok($src, '?') ?: '');

        if ($stripLeadingSlash) {
            $src = ltrim($src, '/');
        }

        if ($src === '') {
            return null;
        }

        return GeneralUtility::getFileAbsFileName($src) ?: null;
    }

    private function readLocalFile(?string $path): ?string
    {
        if ($path === null || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'svg') {
            return null;
        }

        if (!is_file($path) || !is_readable($path)) {
            return null;
        }

        $content = file_get_contents($path);

        return $content === false ? null : $content;
    }

    /**
     * Strips markup that is unwanted in inline SVG.
     *
     * The script/event handler removal is a hardening measure for editor uploaded files,
     * not a complete SVG sanitizer - do not rely on it as a security boundary.
     */
    private function cleanUpSvg(string $svgContent): string
    {
        $patterns = [
            '/<!--(.*?)-->/s',
            '/\s+id="[^"]*"/',
            '/<\?xml.*?\?>/s',
            '/<script\b[^>]*>.*?<\/script>/is',
            '/<script\b[^>]*\/>/i',
            '/\s+on[a-z]+\s*=\s*"[^"]*"/i',
            "/\s+on[a-z]+\s*=\s*'[^']*'/i",
            '/\s+(?:xlink:)?href\s*=\s*"\s*javascript:[^"]*"/i',
            "/\s+(?:xlink:)?href\s*=\s*'\s*javascript:[^']*'/i",
        ];

        return preg_replace($patterns, '', $svgContent) ?? $svgContent;
    }
}
