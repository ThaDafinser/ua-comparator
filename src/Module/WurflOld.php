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
 * @package   UaComparator
 * @author    Thomas Mueller <t_mueller_stolzenhain@yahoo.de>
 * @copyright 2015 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 * @link      https://github.com/mimmi20/ua-comparator
 */

namespace UaComparator\Module;

use Monolog\Logger;
use WURFL_CustomDevice;
use WurflCache\Adapter\AdapterInterface;
use Exception;

/**
 * UaComparator.ini parsing class with caching and update capabilities
 *
 * @category  UaComparator
 * @package   UaComparator
 * @author    Thomas Mueller <t_mueller_stolzenhain@yahoo.de>
 * @copyright 2015 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 */
class WurflOld implements ModuleInterface
{
    /**
     * @var \Monolog\Logger
     */
    private $logger = null;

    /**
     * @var \WurflCache\Adapter\AdapterInterface
     */
    private $cache = null;

    /**
     * @var string
     */
    private $configFile = '';

    /**
     * @var float
     */
    private $timer = 0.0;

    /**
     * @var float
     */
    private $duration = 0.0;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @var \WURFL_CustomDevice|null
     */
    private $detectionResult = null;

    /**
     * creates the module
     *
     * @param \Monolog\Logger                      $logger
     * @param \WurflCache\Adapter\AdapterInterface $cache
     * @param string                               $configFile
     */
    public function __construct(Logger $logger, AdapterInterface $cache, $configFile = '')
    {
        $this->logger     = $logger;
        $this->cache      = $cache;
        $this->configFile = $configFile;
    }

    /**
     * initializes the module
     *
     * @return \UaComparator\Module\WurflOld
     */
    public function init()
    {
        $this->detect('');

        $device = $this->getDetectionResult();
        $device->getAllCapabilities();

        // Create WURFL Configuration from an XML config file
        $wurflConfigOrig  = new \WURFL_Configuration_XmlConfig('data/wurfl-config.xml');
        $wurflCacheOrig   = new \WURFL_Storage_Memory();
        $wurflStorageOrig = new \WURFL_Storage_File(array(\WURFL_Storage_File::DIR => 'data/cache/wurfl_old/'));

        // Create a WURFL Manager Factory from the WURFL Configuration
        $wurflManagerFactoryOrig = new \WURFL_WURFLManagerFactory($wurflConfigOrig, $wurflStorageOrig, $wurflCacheOrig);
        ini_set('max_input_time', '6000');
        // Create a WURFL Manager
        $wurflManagerOrig = $wurflManagerFactoryOrig->create();

        foreach ($wurflManagerOrig->getAllDevicesID() as $deviceId) {
            $result = $wurflManagerOrig->getDevice($deviceId);

            $this->storeProperties($result);
        }

        return $this;
    }

    /**
     * @param string $agent
     *
     * @return \UaComparator\Module\WurflOld
     */
    public function detect($agent)
    {
        // Create WURFL Configuration from an XML config file
        $wurflConfigOrig  = new \WURFL_Configuration_XmlConfig('data/wurfl-config.xml');
        $wurflCacheOrig   = new \WURFL_Storage_Memory();
        $wurflStorageOrig = new \WURFL_Storage_File(array(\WURFL_Storage_File::DIR => 'data/cache/wurfl_old/'));

        // Create a WURFL Manager Factory from the WURFL Configuration
        $wurflManagerFactoryOrig = new \WURFL_WURFLManagerFactory($wurflConfigOrig, $wurflStorageOrig, $wurflCacheOrig);
        ini_set('max_input_time', '6000');
        // Create a WURFL Manager
        $wurflManagerOrig = $wurflManagerFactoryOrig->create();

        $wurflManagerOrig->getAllDevicesID();

        try {
            $this->detectionResult = $wurflManagerOrig->getDeviceForUserAgent($agent);
        } catch (\Exception $e) {
            $this->logger->info($e);

            $this->detectionResult = null;
        }

        return $this;
    }

    /**
     * starts the detection timer
     *
     * @return \UaComparator\Module\WurflOld
     */
    public function startTimer()
    {
        $this->duration = 0.0;
        $this->timer    = microtime(true);

        return $this;
    }

    /**
     * stops the detection timer
     * @return float
     */
    public function endTimer()
    {
        $this->duration = microtime(true) - $this->timer;
        $this->timer    = 0.0;

        return $this;
    }

    /**
     * returns the duration
     *
     * @return float
     */
    public function getTime()
    {
        return $this->duration;
    }

    /**
     * returns the required memory
     *
     * @return int
     */
    public function getMemory()
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return \UaComparator\Module\WurflOld
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return \UaComparator\Module\WurflOld
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return \UaResult\Result
     */
    public function getDetectionResult()
    {
        $mapper = new Mapper\Wurfl();
        return $mapper->map($this->detectionResult, $this->logger);
    }

    /**
     * @var \WURFL_CustomDevice $deviceOrig
     */
    private function storeProperties(WURFL_CustomDevice $deviceOrig = null)
    {
        if (null !== $deviceOrig && !file_exists('data/browser/' . $deviceOrig->id . '.php')) {
            $props = $deviceOrig->getAllCapabilities();

            $content   = "<?php\nreturn " . var_export($props, true) . ";\n";

            file_put_contents('data/browser/' . $deviceOrig->id . '.php', $content);
        }
    }
}
