<?php

/**
 * @see       https://github.com/ecotoneframework/cody-integration for the canonical source repository
 * @license   https://github.com/ecotoneframework/cody-integration/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Ecotone\InspectioCody\Code;

use EventEngine\InspectioCody\Board\Exception\CodyError;
use function count;
use function explode;
use function file_exists;
use function mkdir;

use const DIRECTORY_SEPARATOR;

final class LocalFilesystem implements Filesystem
{
    public function ensurePathExists(string $path): void
    {
        $parts = explode(DIRECTORY_SEPARATOR, $path);

        if (!count($parts)) {
            throw CodyError::withError('Empty path was passed to ' . __METHOD__);
        }

        $subPath = '';

        foreach ($parts as $pathPart) {
            if (empty($pathPart)) {
                continue;
            }

            $subPath = $subPath . DIRECTORY_SEPARATOR . $pathPart;

            if (!file_exists($subPath)) {
                mkdir($subPath);
            }
        }
    }
}
