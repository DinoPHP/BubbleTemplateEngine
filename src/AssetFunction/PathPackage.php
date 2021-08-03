<?php

/**
 * @author Ahmed Mohamed Ibrahim
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace Bubble\AssetFunction;

use Bubble\AssetFunction\Context\ContextInterface;
use Bubble\AssetFunction\VersionStrategy\VersionStrategyInterface;

/**
 * Package that adds a base path to asset URLs in addition to a version.
 *
 * In addition to the provided base path, this package also automatically
 * prepends the current request base path if a Context is available to
 * allow a website to be hosted easily under any given path under the Web
 * Server root directory.
 *
 
 */
class PathPackage extends Package
{
    private $basePath;

    /**
     * @param string $basePath The base path to be prepended to relative paths
     */
    public function __construct(string $basePath, VersionStrategyInterface $versionStrategy, ContextInterface $context = null)
    {
        parent::__construct($versionStrategy, $context);

        if (!$basePath) {
            $this->basePath = '/';
        } else {
            if ('/' != $basePath[0]) {
                $basePath = '/'.$basePath;
            }

            $this->basePath = rtrim($basePath, '/').'/';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(string $path)
    {
        $versionedPath = parent::getUrl($path);

        // if absolute or begins with /, we're done
        if ($this->isAbsoluteUrl($versionedPath) || ($versionedPath && '/' === $versionedPath[0])) {
            return $versionedPath;
        }

        return $this->getBasePath().ltrim($versionedPath, '/');
    }

    /**
     * Returns the base path.
     *
     * @return string The base path
     */
    public function getBasePath()
    {
        return $this->getContext()->getBasePath().$this->basePath;
    }
}
