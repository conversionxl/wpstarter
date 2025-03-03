<?php

/*
 * This file is part of the WP Starter package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace WeCodeMore\WpStarter\Tests;

use Composer;
use Composer\Factory;
use Composer\Util\Filesystem;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use WeCodeMore\WpStarter\Util\Paths;
use WeCodeMore\WpStarter\Util\UrlDownloader;

abstract class IntegrationTestCase extends \PHPUnit\Framework\TestCase
{
    use PhpUnitCrossVersion;

    /**
     * @var OutputInterface[]
     */
    private $outputs = [];

    /**
     * @param int $verbosity
     * @return string
     */
    public function collectOutput(int $verbosity = OutputInterface::VERBOSITY_NORMAL): string
    {
        if ($this->outputs[$verbosity] ?? null) {
            $output = $this->outputs[$verbosity]->output;
            $this->outputs[$verbosity]->output = '';

            return $output;
        }

        return '';
    }

    /**
     * @param int $verbosity
     * @return OutputInterface
     */
    public function createConsoleOutput(
        int $verbosity = OutputInterface::VERBOSITY_NORMAL
    ): OutputInterface {

        if ($this->outputs[$verbosity] ?? null) {
            return $this->outputs[$verbosity];
        }

        $this->outputs[$verbosity] = new class(
            $verbosity,
            false,
            new OutputFormatter(false, Composer\Factory::createAdditionalStyles())
        ) extends Output {

            public $output = '';

            protected function doWrite($message, $newline) // phpcs:ignore
            {
                $this->output .= $message . ($newline ? "\n" : '');
            }
        };

        return $this->outputs[$verbosity];
    }

    /**
     * @param string $input
     * @param int $verbosity
     * @return Composer\IO\IOInterface
     */
    public function createComposerIo(
        string $input = '',
        int $verbosity = OutputInterface::VERBOSITY_NORMAL
    ): Composer\IO\IOInterface {

        return new Composer\IO\ConsoleIO(
            new StringInput($input),
            $this->createConsoleOutput($verbosity),
            new HelperSet()
        );
    }

    /**
     * @param string|null $cwd
     * @param int $verbosity
     * @param string $input
     * @param array $extra
     * @return Paths
     */
    public function createPaths(
        string $cwd = null,
        int $verbosity = OutputInterface::VERBOSITY_NORMAL,
        string $input = '',
        array $extra = []
    ): Paths {

        return $cwd
            ? Paths::withRoot(
                $cwd,
                $this->createComposerConfig($input, $verbosity, $cwd),
                $extra,
                new Composer\Util\Filesystem()
            )
            : new Paths(
                $this->createComposerConfig($input, $verbosity, $cwd),
                $extra,
                new Composer\Util\Filesystem()
            );
    }

    /**
     * @param string $input
     * @param int $verbosity
     * @param string|null $cwd
     * @return Composer\Config
     */
    public function createComposerConfig(
        string $input = '',
        int $verbosity = OutputInterface::VERBOSITY_NORMAL,
        string $cwd = null
    ): Composer\Config {

        return Composer\Factory::createConfig(
            $this->createComposerIo($input, $verbosity),
            $cwd ?? getenv('PACKAGE_PATH')
        );
    }

    /**
     * @return Composer\Composer
     */
    public function createComposer(): Composer\Composer
    {
        $path = getenv('PACKAGE_PATH') . '/composer.json';

        return Composer\Factory::create($this->createComposerIo(), $path, true);
    }

    /**
     * @return UrlDownloader
     */
    public function createUrlDownloader(): UrlDownloader
    {
        $ver = Composer\Composer::RUNTIME_API_VERSION;
        if (version_compare($ver, '2', '<')) {
            return UrlDownloader::newV1(
                Factory::createRemoteFilesystem(
                    $this->createComposerIo(),
                    $this->createComposerConfig()
                ),
                new Filesystem(),
                false
            );
        }

        return UrlDownloader::newV2(
            Factory::createHttpDownloader(
                $this->createComposerIo(),
                $this->createComposerConfig()
            ),
            new Filesystem(),
            false
        );
    }
}
