<?php
namespace Vanio\SymlinkHandler\Tests;

use Composer\Composer;
use Composer\Config;
use Composer\IO\NullIO;
use Composer\Package\RootPackage;
use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;
use Vanio\SymlinkHandler\ScriptHandler;

class ScriptHandlerTest extends \PHPUnit_Framework_TestCase
{
    /** @var Event */
    private $event;

    /** @var RootPackage */
    private $package;

    /** @var NullIO|\PHPUnit_Framework_MockObject_MockObject */
    private $io;

    /** @var Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    private $filesystem;

    protected function setUp()
    {
        $this->package = new RootPackage('package', 'version', 'prettyVersion');
        $composer = new Composer;
        $composer->setPackage($this->package);
        $composer->setConfig(new Config(false, __DIR__ . '/Fixtures'));
        $this->io = $this->getMockBuilder(NullIO::class)->enableProxyingToOriginalMethods()->getMock();
        $this->event = new Event('event', $composer, $this->io);
        $this->filesystem = $this->getMock(Filesystem::class);
    }

    function test_symlinks_creation()
    {
        $this->io
            ->expects($this->exactly(2))
            ->method('write')
            ->withConsecutive(
                ['<info>Creating symlink for "foo" into "bar"</info>'],
                ['<info>Creating symlink for "bar.txt" into "foo.txt"</info>']
            );
        $this->filesystem
            ->expects($this->exactly(2))
            ->method('symlink')
            ->withConsecutive(
                [__DIR__ . '/Fixtures/vendor/foo', __DIR__ . '/Fixtures/bar'],
                [__DIR__ . '/Fixtures/vendor/bar.txt', __DIR__ . '/Fixtures/foo.txt']
            );

        $this->package->setExtra([
            'symlinks' => [
                'foo' => 'bar',
                'bar.txt' => 'foo.txt',
                'invalid' => 'invalid',
            ]
        ]);
        ScriptHandler::createSymlinks($this->event, $this->filesystem);
    }
}
