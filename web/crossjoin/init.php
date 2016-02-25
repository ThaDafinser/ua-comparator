<?php
/**
 * Copyright (c) 2015, Thomas Mueller <t_mueller_stolzenhain@yahoo.de>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  UaComparator
 *
 * @author    Thomas Mueller <t_mueller_stolzenhain@yahoo.de>
 * @copyright 2015 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 *
 * @link      https://github.com/mimmi20/ua-comparator
 */

use Crossjoin\Browscap\Browscap as CrBrowscap;
use Crossjoin\Browscap\Cache\File;
use Crossjoin\Browscap\Updater\Local;

chdir(dirname(__DIR__));

$autoloadPaths = array(
    'vendor/autoload.php',
    '../../autoload.php',
);

foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

ini_set('memory_limit', '-1');

$buildNumber = (int) file_get_contents('vendor/browscap/browscap/BUILD_NUMBER');
$iniFile     = 'data/browscap-ua-test-' . $buildNumber . '/full_php_browscap.ini';

File::setCacheDirectory('data/cache/crossjoin/');
CrBrowscap::setDatasetType(CrBrowscap::DATASET_TYPE_LARGE);

$updater = new Local();
$updater->setOption('LocalFile', $iniFile);
CrBrowscap::setUpdater($updater);
CrBrowscap::update(true);
