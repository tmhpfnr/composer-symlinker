<?php

namespace ComposerLocalInstaller;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

class LocalInstaller extends LibraryInstaller
{

    protected $localPath;
    protected $localPrefixes;

    public function __construct(IOInterface $io, Composer $composer, $type = 'library', Filesystem $filesystem = null)
    {
        parent::__construct($io, $composer, $type, $filesystem);
        $extra                  = $composer->getPackage()->getExtra();
        $local_path             = isset($extra['local-path']) ? rtrim($extra['local-path'], '/') : dirname(__DIR__);
        if (!file_exists($local_path)) {
            throw new \InvalidArgumentException('Local path not found: '.$local_path);
        } else {
            $this->localPath    = $local_path;
        }
        $local_prefixes         = isset($extra['local-prefixes']) ? $extra['local-prefixes'] : null;
        $this->localPrefixes    = is_array($local_prefixes) ? $local_prefixes : array($local_prefixes);

$this->io->writeError('<warning>local path defined on '.$this->localPath.'</warning>');
$this->io->writeError('<warning>prefixes defined on '.var_export($this->localPrefixes,true).'</warning>');

    }

    public function supports($packageType)
    {
        return parent::supports($packageType);
    }

    public function isConcerned(PackageInterface $package)
    {
$this->io->writeError('<warning>is concerned '.$package->getPrettyName().' ???</warning>');
        $ok = false;
        foreach ($this->localPrefixes as $prefix) {
            if (
                substr($package->getPrettyName(), 0, strlen($prefix)) === $prefix
                &&
                file_exists($this->localPath . '/' . $package->getPrettyName())
            ) {
                $ok = true;
            }
        }
$this->io->writeError('<warning>=> '.var_export($ok, true).'</warning>');
        return $ok;
    }

    protected function installCode(PackageInterface $package)
    {
        if ($this->isConcerned($package)) {
            symlink($this->localPath . '/' . $package->getPrettyName(), $this->getInstallPath($package));
            return true;
        }
        return parent::installCode($package);
    }

    protected function updateCode(PackageInterface $initial, PackageInterface $target)
    {
        if ($this->isConcerned($target)) {
            $this->filesystem->rename($this->getInstallPath($initial), $this->getInstallPath($target));
            return true;
        }
        return parent::updateCode($initial, $target);
    }

    protected function removeCode(PackageInterface $package)
    {
        if ($this->isConcerned($package)) {
            $this->filesystem->unlink($this->getInstallPath($package));
            return true;
        }
        return parent::removeCode($package);
    }

}
