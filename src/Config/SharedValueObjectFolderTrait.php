<?php

/**
 * @see       https://github.com/ecotoneframework/cody-integration for the canonical source repository
 * @license   https://github.com/ecotoneframework/cody-integration/blob/master/LICENSE.md MIT License
 */

declare(strict_types=1);

namespace Ecotone\InspectioCody\Config;

trait SharedValueObjectFolderTrait
{
    private string $sharedValueObjectFolder;

    /**
     * @return string
     */
    public function sharedValueObjectFolder(): string
    {
        return $this->sharedValueObjectFolder;
    }

    /**
     * @param string $sharedValueObjectFolder
     */
    public function setSharedValueObjectFolder(string $sharedValueObjectFolder): void
    {
        $this->sharedValueObjectFolder = $sharedValueObjectFolder;
    }
}
