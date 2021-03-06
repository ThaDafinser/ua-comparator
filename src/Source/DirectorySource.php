<?php

namespace UaComparator\Source;

use BrowscapPHP\Helper\IniLoader;
use Monolog\Logger;

/**
 * Class DirectorySource
 *
 * @author  Thomas Mueller <t_mueller_stolzenhain@yahoo.de>
 */
class DirectorySource implements SourceInterface
{
    /**
     * @var string
     */
    private $dir = null;

    /**
     * @param string $dir
     */
    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * @param \Monolog\Logger $logger
     * @param int             $limit
     *
     * @throws \BrowscapPHP\Helper\Exception
     *
     * @return \Generator
     */
    public function getUserAgents(Logger $logger, $limit = 0)
    {
        $iterator = new \RecursiveDirectoryIterator($this->dir);
        $loader   = new IniLoader();
        $allLines = [];

        foreach (new \RecursiveIteratorIterator($iterator) as $file) {
            /** @var $file \SplFileInfo */
            if (!$file->isFile()) {
                continue;
            }

            $path = $file->getPathname();

            $loader->setLocalFile($path);
            $internalLoader = $loader->getLoader();

            if ($internalLoader->isSupportingLoadingLines()) {
                if (!$internalLoader->init($path)) {
                    $logger->info('Skipping empty file "' . $file->getPathname() . '"');
                    continue;
                }

                while ($internalLoader->isValid()) {
                    $line = $internalLoader->getLine();

                    if (isset($allLines[$line])) {
                        continue;
                    }

                    $allLines[$line] = 1;

                    yield $line;

                    if ($limit && count($allLines) >= $limit) {
                        return;
                    }
                }

                $internalLoader->close();
            } else {
                $lines = file($path);

                if (empty($lines)) {
                    $logger->info('Skipping empty file "' . $file->getPathname() . '"');
                    continue;
                }

                foreach ($lines as $line) {
                    if (isset($allLines[$line])) {
                        continue;
                    }

                    $allLines[$line] = 1;

                    yield $line;

                    if ($limit && count($allLines) >= $limit) {
                        return;
                    }
                }
            }
        }
    }
}
