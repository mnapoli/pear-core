--TEST--
PEAR_Downloader->download() with complex local package.xml [onlyreqdeps, preferred_state = alpha]
--SKIPIF--
<?php
if (!getenv('PHP_PEAR_RUNTESTS')) {
    echo 'skip';
}
?>
--FILE--
<?php
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'setup.php.inc';
$pathtopackagexml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'depspackage.xml';
$pathtobarxml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'Bar-1.5.1.tgz';
$pathtofoobarxml = dirname(__FILE__)  . DIRECTORY_SEPARATOR .
    'packages'. DIRECTORY_SEPARATOR . 'Foobar-1.4.0a1.tgz';
$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/Bar-1.5.1.tgz', $pathtobarxml);
$GLOBALS['pearweb']->addHtmlConfig('http://www.example.com/Foobar-1.4.0a1.tgz', $pathtofoobarxml);
$GLOBALS['pearweb']->addXmlrpcConfig('pear.php.net', 'package.getDepDownloadURL',
    array('1.0',
         array('type' => 'pkg', 'rel' => 'ge', 'version' => '1.0.0',
               'name' => 'Bar', 'channel' => 'pear.php.net', 'package' => 'Bar'),
         array('channel' => 'pear.php.net', 'package' => 'PEAR1', 'version' => '1.4.0a1'), 'alpha'),
    array('version' => '1.5.1',
          'info' =>
          array(
            'package' => 'Bar',
            'channel' => 'pear.php.net',
            'license' => 'PHP License',
            'summary' => 'test',
            'description' => 'test',
            'releasedate' => '2003-12-06 00:26:42',
            'state' => 'stable',
            'deps' =>
            array(
                array(
                    'type' => 'pkg',
                    'rel' => 'has',
                    'name' => 'Foobar',
                    'optional' => 'yes',
                )
            ),
          ),
          'url' => 'http://www.example.com/Bar-1.5.1'));
$GLOBALS['pearweb']->addXmlrpcConfig('pear.php.net', 'package.getDepDownloadURL',
    array('1.0',
         array('type' => 'pkg', 'rel' => 'has',
               'name' => 'Foobar', 'optional' => 'yes',
               'channel' => 'pear.php.net', 'package' => 'Foobar'),
         array('channel' => 'pear.php.net', 'package' => 'Bar', 'version' => '1.5.1'), 'alpha'),
    array('version' => '1.4.0a1',
          'info' =>
          array(
            'package' => 'Foobar',
            'channel' => 'pear.php.net',
            'license' => 'PHP License',
            'summary' => 'test',
            'description' => 'test',
            'releasedate' => '2003-12-06 00:26:42',
            'state' => 'alpha',
          ),
          'url' => 'http://www.example.com/Foobar-1.4.0a1'));
$_test_dep->setPHPVersion('4.3.11');
$_test_dep->setPEARVersion('1.4.0a1');
$dp = &new test_PEAR_Downloader($fakelog, array('onlyreqdeps' => true), $config);
$phpunit->assertNoErrors('after create');
$config->set('preferred_state', 'alpha');
$result = &$dp->download(array($pathtopackagexml));
$phpunit->assertEquals(2, count($result), 'return');
$phpunit->assertIsa('test_PEAR_Downloader_Package', $result[0], 'right class 0');
$phpunit->assertIsa('PEAR_Downloader_Package', $result[1], 'right class 1');
$phpunit->assertIsa('PEAR_PackageFile_v1', $pf = $result[0]->getPackageFile(), 'right kind of pf 0');
$phpunit->assertIsa('PEAR_PackageFile_v1', $pf1 = $result[1]->getPackageFile(), 'right kind of pf 1');
$phpunit->assertEquals('PEAR1', $pf->getPackage(), 'right package');
$phpunit->assertEquals('pear.php.net', $pf->getChannel(), 'right channel');
$phpunit->assertEquals('Bar', $pf1->getPackage(), 'right package 1');
$phpunit->assertEquals('pear.php.net', $pf1->getChannel(), 'right channel 1');
$dlpackages = $dp->getDownloadedPackages();
$phpunit->assertEquals(2, count($dlpackages), 'downloaded packages count');
$phpunit->assertEquals(3, count($dlpackages[0]), 'internals package count');
$phpunit->assertEquals(3, count($dlpackages[1]), 'internals package count 1');
$phpunit->assertEquals(array('file', 'info', 'pkg'), array_keys($dlpackages[0]), 'indexes');
$phpunit->assertEquals(array('file', 'info', 'pkg'), array_keys($dlpackages[1]), 'indexes 1');
$phpunit->assertEquals($pathtopackagexml,
    $dlpackages[0]['file'], 'file');
$phpunit->assertIsa('PEAR_PackageFile_v1',
    $dlpackages[0]['info'], 'info');
$phpunit->assertEquals('PEAR1',
    $dlpackages[0]['pkg'], 'PEAR1');
$phpunit->assertEquals($result[1]->_downloader->getDownloadDir() . DIRECTORY_SEPARATOR .
    'Bar-1.5.1.tgz',
    $dlpackages[1]['file'], 'file 1');
$phpunit->assertIsa('PEAR_PackageFile_v1',
    $dlpackages[1]['info'], 'info 1');
$phpunit->assertEquals('Bar',
    $dlpackages[1]['pkg'], 'Bar');
$after = $dp->getDownloadedPackages();
$phpunit->assertEquals(0, count($after), 'after getdp count');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 3,
    1 => '+ tmp dir created at ' . $dp->getDownloadDir(),
  ),
  1 =>
  array (
    0 => 3,
    1 => 'Notice: package "pear/Bar" optional dependency "pear/Foobar" will not be automatically downloaded',
  ),
  2 =>
  array (
    0 => 1,
    1 => 'Did not download dependencies: pear/Foobar, use --alldeps or --onlyreqdeps to download automatically',
  ),
  3 =>
  array (
    0 => 0,
    1 => 'pear/Bar can optionally use package "pear/Foobar"',
  ),
  4 => 
  array (
    0 => 1,
    1 => 'downloading Bar-1.5.1.tgz ...',
  ),
  5 => 
  array (
    0 => 1,
    1 => 'Starting to download Bar-1.5.1.tgz (610 bytes)',
  ),
  6 => 
  array (
    0 => 1,
    1 => '.',
  ),
  7 => 
  array (
    0 => 1,
    1 => '...done: 610 bytes',
  ),
), $fakelog->getLog(), 'log messages');
$phpunit->assertEquals(array (
  0 => 
  array (
    0 => 'setup',
    1 => 'self',
  ),
  1 => 
  array (
    0 => 'saveas',
    1 => 'Bar-1.5.1.tgz',
  ),
  2 => 
  array (
    0 => 'start',
    1 => 
    array (
      0 => 'Bar-1.5.1.tgz',
      1 => '610',
    ),
  ),
  3 => 
  array (
    0 => 'bytesread',
    1 => 610,
  ),
  4 => 
  array (
    0 => 'done',
    1 => 610,
  ),
), $fakelog->getDownload(), 'download callback messages');
echo 'tests done';
?>
--EXPECT--
tests done