<?php
namespace Vanio\SymlinkHandler;

use Composer\Config;
use Composer\Package\PackageInterface;
use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;

class ScriptHandler
{
    public static function createSymlinks(Event $event, Filesystem $filesystem = null)
    {
        /** @var PackageInterface $package */
        $package = $event->getComposer()->getPackage();
        /** @var Config $config */
        $config = $event->getComposer()->getConfig();
        $symlinks = (array) $package->getExtra()['symlinks'] ?? [];
        $vendorPath = $config->get('vendor-dir');
        $rootPath = dirname($vendorPath);
        $filesystem = $filesystem ?: new Filesystem;

        foreach ($symlinks as $sourceRelativePath => $targetRelativePath) {
            $sourceAbsolutePath = sprintf('%s/%s', $vendorPath, $sourceRelativePath);

            if (!file_exists($sourceAbsolutePath)) {
                continue;
            }

            $event->getIO()->write(sprintf(
                '<info>Creating symlink for "%s" into "%s"</info>',
                $sourceRelativePath,
                $targetRelativePath
            ));

            $filesystem->symlink($sourceAbsolutePath, sprintf('%s/%s', $rootPath, $targetRelativePath));
        }
    }
}
